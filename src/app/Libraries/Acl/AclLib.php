<?php

namespace App\Libraries\Acl;

use App\Libraries\System\SystemLib;
use App\Models\Acl\GroupPermissionModel;
use Config\Database;
use Exception;

class AclLib
{
    protected $db;
    protected $sys_lib;
    protected $group_permission;

    protected $unused_menu_codes = [
        'elderly',
        'elderly_master',
        'elderly_activity',
        'elderly_activity_type',
        'elderly_disability',
        'elderly_disability_type',
        'elderly_inmates',
        'elderly_regional_organizations',
        'elderly_technical_unit',
    ];

    function __construct()
    {
        $this->db               = Database::connect();
        $this->sys_lib          = new SystemLib();
        $this->group_permission = new GroupPermissionModel();
    }

    public function get_menu_module_permissions()
    {
        // Ambil semua menu dan simpan dalam array dengan key parent_id
        $all_menus = $this->db->table('acl.menu')
            ->select('id, menu_code, menu_name, parent_id, sequence_no')
            ->orderBy("CASE WHEN menu_code = 'dashboard' THEN 0 ELSE 1 END", 'ASC', false)
            ->orderBy('sequence_no', 'ASC')
            ->get()
            ->getResultArray();

        $menu_tree = [];

        foreach ($all_menus as $menu) {
            $menu_tree[$menu['parent_id']][] = $menu;
        }

        // Fungsi rekursif untuk membangun struktur menu & permission
        $buildMenuTree = function ($parentId) use (&$buildMenuTree, $menu_tree) {
            $result = [];

            if (!isset($menu_tree[$parentId])) {
                return $result;
            }

            foreach ($menu_tree[$parentId] as $menu) {
                if (in_array($menu['menu_code'], $this->unused_menu_codes, true)) {
                    continue;
                }

                $submenu_code = $menu['menu_code'];
                $submenu_name = $menu['menu_name'];

                // Ambil permission untuk menu ini
                $permissions = $this->db->table('acl.permissions ap')
                    ->select('ap.id, ap.action_code, aa.action_name, aa.indicator')
                    ->join('acl.actions aa', 'ap.action_code = aa.action_code', 'inner')
                    ->where('ap.menu_code', $submenu_code)
                    ->orderBy('ap.sequence_no', 'ASC')
                    ->get()
                    ->getResultArray();

                // Build struktur menu saat ini
                $result[$submenu_code] = [
                    'menu_name'   => $submenu_name,
                    'permissions' => $permissions,
                    'menus'       => $buildMenuTree($menu['id']) // rekursif ke sub-sub menu
                ];
            }

            return $result;
        };

        // Bangun menu tree mulai dari parent_id NULL (main menu)
        $menu_structure = $buildMenuTree(null);

        // dd($menu_structure); // Debug kalau perlu
        return $menu_structure;
    }

    public function assign_group_permissions($group_id, $permissions)
    {
        if (empty($permissions)) {
            throw new Exception('Silahkan masukkan akses kontrol.');
        }

        // Kelola transaksi sendiri hanya jika tidak sedang berada dalam transaksi lain
        $own_transaction = ($this->db->transDepth === 0);

        if ($own_transaction) {
            $this->db->transBegin();
        }

        try {
            // Remove permission by group
            $this->group_permission->where('group_id', $group_id)->delete();

            // Assign permission
            $data = array_map(fn($permission) => [
                'id'            => uuid_v4(),
                'group_id'      => $group_id,
                'permission_id' => $permission,
            ], $permissions);

            if (!$this->group_permission->insertBatch($data)) {
                throw new Exception('Gagal menyimpan akses kontrol.');
            }

            if ($own_transaction) {
                $this->db->transCommit();
            }
        } catch (Exception $e) {
            if ($own_transaction) {
                $this->db->transRollback();
            }

            throw $e;
        }
    }

    public function get_access($user_id = NULL, $menu_code, $action_code)
    {
        $permissions = $this->db->table('acl.permissions ap')
            ->select('au.id as user_id, ap.menu_code, ap.action_code')
            ->join('acl.group_permissions agp', 'agp.permission_id = ap.id', 'inner')
            ->join('acl.groups ag', 'agp.group_id = ag.id', 'inner')
            ->join('acl.user_groups aug', 'aug.group_id = ag.id', 'inner')
            ->join('acl.users au', 'aug.user_id = au.id', 'inner')
            ->where('ap.menu_code', $menu_code)
            ->where('ap.action_code', $action_code)
            ->where('au.id', $user_id)
            ->get()
            ->getResultArray();

        $org_raw = $this->sys_lib->get_sys_reference('allowed_organization_configuration');
        $org_array = json_decode(str_replace("'", '"', $org_raw), true);

        if (in_array($user_id, $org_array ?? [])) {
            return true;
        }

        return !empty($permissions);
    }

    public function get_menu_structure($user_id = NULL)
    {
        $allowed_menus = $this->db->table('acl.menu am')
            ->select('am.id, am.menu_code, am.menu_name, am.parent_id, am.module_id, am.url, am.sequence_no, am.icon_name, am.is_display')
            ->distinct()
            ->join('acl.permissions ap', 'ap.menu_code = am.menu_code', 'inner')
            ->join('acl.group_permissions agp', 'agp.permission_id = ap.id', 'inner')
            ->join('acl.groups ag', 'agp.group_id = ag.id', 'inner')
            ->join('acl.user_groups aug', 'aug.group_id = ag.id', 'inner')
            ->join('acl.users au', 'aug.user_id = au.id', 'inner')
            ->where('au.id', $user_id)
            ->orderBy('am.sequence_no', 'ASC')
            ->get()
            ->getResultArray();

        $parent_ids = array_column($allowed_menus, 'parent_id');
        $parent_ids = array_filter(array_unique($parent_ids));

        if (!empty($parent_ids)) {
            $parent_menus = $this->db->table('acl.menu')
                ->select('id, menu_code, menu_name, parent_id, module_id, url, sequence_no, icon_name, is_display')
                ->whereIn('id', $parent_ids)
                ->get()
                ->getResultArray();

            $allowed_menus = array_merge($allowed_menus, $parent_menus);
        }

        $menu_tree = $this->build_menu_tree($allowed_menus);

        $render = $this->render_menu($menu_tree);

        return $render;
    }

    private function build_menu_tree($menus)
    {
        $menu_tree = [];
        $menu_map = [];

        foreach ($menus as $menu) {
            $menu['children'] = [];
            $menu_map[$menu['id']] = $menu;
        }

        // Create Menu Structure
        foreach ($menu_map as &$menu) {
            if ($menu['parent_id'] && isset($menu_map[$menu['parent_id']])) {
                // If Parent Exists add menu as child
                $menu_map[$menu['parent_id']]['children'][] = &$menu;
            } else {
                // Menu without parent (root menu)
                $menu_tree[] = &$menu;
            }
        }

        return $menu_tree;
    }

    private function render_menu($menu_tree)
    {
        foreach ($menu_tree as $menu) {
            $has_children = !empty($menu['children']);
            $menu_url = !empty($menu['url']) ? site_url($menu['url']) : '#';

            $uri = service('uri')->getSegments();
            $uri2 = $uri[1] ?? '';

            $menu_uri = isset($menu['url']) ? explode('/', trim($menu['url'], '/')) : [];

            $is_active = (!empty($menu_uri[1]) && $uri2 === $menu_uri[1]);
            $is_expanded = $has_children ? 'data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebar-' . $menu['menu_code'] . '"' : '';

            echo '<li class="nav-item">';

            // If menu have child (submenu)
            if ($has_children) {
                echo '<a class="nav-link menu-link ' . ($is_active ? 'active' : '') . '" href="#sidebar-' . $menu['menu_code'] . '" ' . $is_expanded . '>
                    ' . (!empty($menu['icon_name']) ? '<i class="' . $menu['icon_name'] . '"></i>' : '') . '
                    <span data-key="t-' . strtolower(str_replace(' ', '-', $menu['menu_code'])) . '">' . $menu['menu_name'] . '</span>
                </a>';

                echo '<div class="collapse menu-dropdown ' . ($is_active ? 'show' : '') . '" id="sidebar-' . $menu['menu_code'] . '">';
                echo '<ul class="nav nav-sm flex-column">';
                $this->render_menu($menu['children']); // Call Recursive for submenu
                echo '</ul>';
                echo '</div>';
            } else {
                // If menu not have child
                echo '<a class="nav-link ' . ($is_active ? 'active' : '') . '" href="' . $menu_url . '">
                        ' . (!empty($menu['icon_name']) ? '<i class="' . $menu['icon_name'] . '"></i>' : '') . '
                        <span data-key="t-' . strtolower(str_replace(' ', '-', $menu['menu_code'])) . '">' . $menu['menu_name'] . '</span>
                      </a>';
            }

            echo '</li>';
        }
    }
}
