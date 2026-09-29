<div class="col-sm-12 col-lg-7" data-aos="fade-left">
    <div class="card mb-4">
        <h5 class="card-header d-flex align-items-center gap-1"><i class="ri-lock-2-line"></i> Akses Kontrol</h5>
        <div class="card-body">
            <div class="row mb-2">
                <?php foreach ($actions as $action) : ?>
                    <div class="row mb-2">
                        <div class="col-sm-2 fw-bold"><?php echo $action['action_name'] ?></div>
                        <div class="col-sm-10"><?php echo $action['description'] ?></div>
                    </div>
                <?php endforeach ?>
            </div>

            <table class="table" id="table_access_acl">
                <thead>
                    <tr>
                        <td class="text-center fw-bold">Menu</td>
                        <td class="text-center fw-bold">Aksi</td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permission_list as $main_menu_code => $main_menu) : ?>
                        <?php
                        $safe_main_code = preg_replace('/[^a-zA-Z0-9_]/', '_', $main_menu_code);
                        $existing_permissions = array_column($group_permissions, 'permission_id');
                        ?>
                        <?php if (empty($main_menu['menus'])) : ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php if ($read_only) : ?>
                                        <?php echo $main_menu['menu_name']; ?>
                                    <?php else : ?>
                                        <?php
                                        $options = [
                                            'id'    => 'parent_' . $safe_main_code,
                                            'class' => 'form-check-input me-1',
                                        ];
                                        echo form_checkbox('menu_code[parent_' . $safe_main_code . ']', $main_menu_code, false, $options);
                                        echo form_label($main_menu['menu_name'], 'parent_' . $safe_main_code, ['class' => 'form-check-label']);
                                        ?>
                                    <?php endif; ?>
                                </td>
                                <td class="d-flex flex-wrap gap-3">
                                    <?php foreach ($main_menu['permissions'] as $permission) :
                                        $is_checked = in_array($permission['id'], $existing_permissions);
                                        $options = [
                                            'id'    => 'permission_' . $permission['id'],
                                            'class' => 'form-check-input child_' . $safe_main_code,
                                            'value' => $permission['id'],
                                        ];

                                        if ($read_only) {
                                            echo '<div>';
                                            echo $is_checked
                                                ? '<span class="badge bg-' . $permission['indicator'] . '">' . $permission['action_name'] . '</span>'
                                                : '<span>&nbsp;</span>';
                                            echo '</div>';
                                        } else {
                                            echo '<div class="form-check form-switch">';
                                            echo form_checkbox('permissions[]', $permission['id'], $is_checked, $options);
                                            echo form_label($permission['action_name'], 'permission_' . $permission['id'], ['class' => 'form-check-label']);
                                            echo '</div>';
                                        }
                                    endforeach; ?>
                                </td>
                            </tr>
                        <?php else : ?>
                            <tr>
                                <td colspan="2" class="fw-bold"><?php echo $main_menu['menu_name']; ?></td>
                            </tr>
                            <?php foreach ($main_menu['menus'] as $menu_code => $menu) :
                                render_menu_row($menu, $menu_code, $group_permissions, $read_only, 1);
                            endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>