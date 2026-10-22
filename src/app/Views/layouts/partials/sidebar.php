<?php
$uri = service('uri')->getSegments();
$uri1 = $uri[0] ?? 'index';
$uri2 = $uri[1] ?? '';
$uri3 = $uri[2] ?? '';
$is_admin = empty(session()->get('login_personnel_id'));

$can_view = function (string $menu_code): bool {
    return is_authorized($menu_code, 'view');
};
$any_view = function (array $menu_codes): bool {
    foreach ($menu_codes as $menu_code) {
        if (is_authorized($menu_code, 'view')) {
            return true;
        }
    }
    return false;
};

$show_penilaian    = $any_view(['assessment_component', 'component_aspect', 'aspect_question']);
$show_peta_jabatan = $any_view(['employee_grade', 'employee_assortment', 'instance_position_grade', 'instance_position_group', 'instance_position', 'instance_field_classification', 'instance_class']);
$show_instansi     = $any_view(['instance_position_structure', 'instance_organization_structure', 'instance_organization', 'instance_organization_parent', 'instance_other_organization_parent', 'instance_other_organization', 'instance_group']);
$show_wilayah      = $any_view(['region_province', 'region_city', 'region_district', 'region_village']);
$show_pengguna     = $any_view(['acl_user', 'acl_group', 'acl_permission', 'acl_menu', 'acl_action', 'acl_icon']);
$show_referensi    = $any_view(['reference_employment_type', 'reference_employment', 'reference_indicator']);
$show_system       = $any_view(['system_module', 'system_language', 'system_language_field', 'system_reference', 'system_configuration']);
$show_log          = $any_view(['log_login', 'log_access', 'log_change']);
$show_rekap        = $can_view('recap');
$show_pengaturan   = $can_view('interview_group') || $show_penilaian || $show_peta_jabatan || $show_instansi || $show_wilayah || $show_pengguna || $show_referensi || $show_system || $show_log;
?>

<!-- Sidebar -->
<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="<?php echo site_url('/home/dashboard'); ?>" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo base_url('vlz/images/logo-sm.png') ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo base_url('vlz/images/logo-dark.png') ?>" alt="" height="20">
            </span>
        </a>
        <a href="<?php echo site_url('/home/dashboard'); ?>" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo base_url('vlz/images/logo-sm.png') ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo base_url('vlz/images/logo.png') ?>" alt="" height="20">
            </span>
        </a>
    </div>

    <div id="scrollbar" data-simplebar class="h-100">
        <div id="two-column-menu"></div>
        <ul class="navbar-nav" id="navbar-nav">

            <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?php echo $uri1 == 'home' ? 'active' : '' ?>" href="<?php echo site_url('home/dashboard'); ?>">
                        <i class="bx bx-home-circle"></i> <span>Laman</span>
                    </a>
                </li>

                <?php if ($can_view('employee_personnel')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'employee' && $uri2 == 'personnel') ? 'active' : '' ?>" href="<?php echo site_url('employee/personnel'); ?>">
                            <i class="bx bx-group"></i> <span>Data Pegawai</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view('interview_group') || $can_view('interview_participant')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo (($uri1 === 'interview' && $uri2 === 'interview_group' && $uri3 === 'list') || ($uri1 === 'interview_participant')) ? 'active' : '' ?>" href="<?php echo site_url('interview/interview_group/list'); ?>">
                            <i class="bx bxs-user-account"></i> <span>Peserta Wawancara</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view('monitoring_verification')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'monitoring' && $uri2 == 'verification') ? 'active' : '' ?>" href="<?php echo site_url('monitoring/verification'); ?>">
                            <i class="bx bx-check-shield"></i> <span>Monitoring Verifikasi</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($show_rekap): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['recap']) ? 'active' : '' ?>" href="#Rekapitulasi" data-bs-toggle="collapse">
                            <i class="bx bx-folder-open"></i> <span>Rekapitulasi</span>
                        </a>
                        <div id="Rekapitulasi" class="collapse menu-dropdown <?php echo $uri1 == 'recap' ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="<?php echo site_url('recap/recap'); ?>" class="nav-link <?php echo $uri1 == 'recap' && $uri2 == 'recap' && $uri3 == '' ? 'active' : '' ?>">Kelompok</a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo site_url('recap/recap/all'); ?>" class="nav-link <?php echo $uri1 == 'recap' && $uri2 == 'recap' && $uri3 == 'all' ? 'active' : '' ?>">Keseluruhan</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_pengaturan): ?>
                    <li class="menu-title"><span>Pengaturan</span></li>
                <?php endif; ?>

                <?php if ($can_view('interview_group')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo (($uri1 === 'interview' && $uri2 === 'interview_group' && $uri3 !== 'list') || ($uri1 === 'interview_participant' && $uri2 === 'list')) ? 'active' : '' ?>" href="<?php echo site_url('interview/interview_group'); ?>">
                            <i class="bx bxs-user-account"></i> <span>Kelompok Wawancara</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($show_penilaian): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['assessment_component', 'component_aspect', 'aspect_question', 'question_instrument']) ? 'active' : '' ?>" href="#Penilaian" data-bs-toggle="collapse">
                            <i class="bx bxs-report"></i> <span>Penilaian</span>
                        </a>
                        <div id="Penilaian" class="collapse menu-dropdown <?php echo in_array($uri2, ['assessment_component', 'component_aspect', 'aspect_question', 'question_instrument']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('assessment_component')): ?>
                                    <li class="nav-item">
                                        <a href="<?php echo site_url('assessment/assessment_component'); ?>" class="nav-link <?php echo $uri2 == 'assessment_component' ? 'active' : '' ?>">Komponen Penilaian</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_peta_jabatan): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['grades', 'assortments', 'position_grade', 'position_groups', 'position', 'positions', 'field_classifications', 'position_classifications', 'classes']) ? 'active' : '' ?>" href="#PetaJabatan" data-bs-toggle="collapse">
                            <i class="bx bx-sitemap"></i> <span>Peta Jabatan</span>
                        </a>
                        <div id="PetaJabatan" class="collapse menu-dropdown <?php echo in_array($uri2, ['grades', 'assortments', 'position_grade', 'position_groups', 'position', 'positions', 'field_classifications', 'position_classifications', 'classes']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('employee_grade')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('employee/grades'); ?>" class="nav-link <?php echo ($uri1 == 'employee' && $uri2 == 'grades') ? 'active' : '' ?>">Tingkatan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('employee_assortment')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('employee/assortments'); ?>" class="nav-link <?php echo $uri2 == 'assortments' ? 'active' : '' ?>">Pangkat/Golongan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_position_group')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/position_groups'); ?>" class="nav-link <?php echo $uri2 == 'position_groups' ? 'active' : '' ?>">Kelompok Jabatan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_position')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/positions'); ?>" class="nav-link <?php echo ($uri2 == 'position' || $uri2 == 'positions') ? 'active' : '' ?>">Jabatan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_field_classification')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/field_classifications'); ?>" class="nav-link <?php echo ($uri2 == 'field_classifications' || $uri2 == 'position_classifications') ? 'active' : '' ?>">Rumpun Jabatan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_class')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/classes'); ?>" class="nav-link <?php echo $uri2 == 'classes' ? 'active' : '' ?>">Kelas Jabatan</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_instansi): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['position_structures', 'organization_structures', 'organizations', 'organization_parent', 'other_organization_parent', 'other_organizations', 'groups', 'instance']) ? 'active' : '' ?>" href="#Instansi" data-bs-toggle="collapse">
                            <i class="bx bx-building"></i> <span>Instansi</span>
                        </a>
                        <div id="Instansi" class="collapse menu-dropdown <?php echo in_array($uri2, ['position_structures', 'organization_structures', 'organizations', 'organization_parent', 'other_organization_parent', 'other_organizations', 'groups', 'instance']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('instance_position_structure')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/position_structures'); ?>" class="nav-link <?php echo $uri2 == 'position_structures' ? 'active' : '' ?>">Struktur Jabatan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_organization_structure')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/organization_structures'); ?>" class="nav-link <?php echo $uri2 == 'organization_structures' ? 'active' : '' ?>">Struktur Organisasi</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_organization')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/organizations'); ?>" class="nav-link <?php echo $uri2 == 'organizations' ? 'active' : '' ?>">Unit Kerja</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_organization_parent')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/organization_parent'); ?>" class="nav-link <?php echo $uri2 == 'organization_parent' ? 'active' : '' ?>">Instansi</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_other_organization_parent')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/other_organization_parent'); ?>" class="nav-link <?php echo $uri2 == 'other_organization_parent' ? 'active' : '' ?>">Instansi Lainnya</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_other_organization')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/other_organizations'); ?>" class="nav-link <?php echo $uri2 == 'other_organizations' ? 'active' : '' ?>">Unit Kerja Instansi Lainnya</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('instance_group')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('instance/groups'); ?>" class="nav-link <?php echo ($uri2 == 'groups' && $uri1 == 'instance') ? 'active' : '' ?>">Jenis Instansi</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_wilayah): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['provinces', 'cities', 'district', 'village']) ? 'active' : '' ?>" href="#Wilayah" data-bs-toggle="collapse">
                            <i class="bx bx-map"></i> <span>Wilayah</span>
                        </a>
                        <div id="Wilayah" class="collapse menu-dropdown <?php echo in_array($uri2, ['provinces', 'cities', 'district', 'village']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('region_province')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('region/provinces'); ?>" class="nav-link <?php echo $uri2 == 'provinces' ? 'active' : '' ?>">Provinsi</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('region_city')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('region/cities'); ?>" class="nav-link <?php echo $uri2 == 'cities' ? 'active' : '' ?>">Kota</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('region_district')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('region/district'); ?>" class="nav-link <?php echo $uri2 == 'district' ? 'active' : '' ?>">Kecamatan</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('region_village')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('region/village'); ?>" class="nav-link <?php echo $uri2 == 'village' ? 'active' : '' ?>">Kelurahan</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_pengguna): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'acl' && in_array($uri2, ['users', 'groups', 'permissions', 'menu', 'actions', 'icons'])) ? 'active' : '' ?>" href="#Pengguna" data-bs-toggle="collapse">
                            <i class="bx bx-user"></i> <span>Pengguna</span>
                        </a>
                        <div id="Pengguna" class="collapse menu-dropdown <?php echo ($uri1 == 'acl' && in_array($uri2, ['users', 'groups', 'permissions', 'menu', 'actions', 'icons'])) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('acl_user')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('acl/users'); ?>" class="nav-link <?php echo ($uri1 == 'acl' && $uri2 == 'users') ? 'active' : '' ?>">Daftar Pengguna</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('acl_group')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('acl/groups'); ?>" class="nav-link <?php echo ($uri1 == 'acl' && $uri2 == 'groups') ? 'active' : '' ?>">Grup Akses</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('acl_permission')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('acl/permissions'); ?>" class="nav-link <?php echo ($uri1 == 'acl' && $uri2 == 'permissions') ? 'active' : '' ?>">Permission</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('acl_menu')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('acl/menu'); ?>" class="nav-link <?php echo ($uri1 == 'acl' && $uri2 == 'menu') ? 'active' : '' ?>">Menu</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('acl_action')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('acl/actions'); ?>" class="nav-link <?php echo ($uri1 == 'acl' && $uri2 == 'actions') ? 'active' : '' ?>">Actions</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('acl_icon')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('acl/icons'); ?>" class="nav-link <?php echo ($uri1 == 'acl' && $uri2 == 'icons') ? 'active' : '' ?>">Icons</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_referensi): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['employment_type', 'employment', 'indicators']) ? 'active' : '' ?>" href="#Referensi" data-bs-toggle="collapse">
                            <i class="bx bx-link"></i> <span>Referensi</span>
                        </a>
                        <div id="Referensi" class="collapse menu-dropdown <?php echo in_array($uri2, ['employment_type', 'employment', 'indicators']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('reference_employment_type')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('reference/employment_type'); ?>" class="nav-link <?php echo $uri2 == 'employment_type' ? 'active' : '' ?>">Tipe Pegawai</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('reference_employment')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('reference/employment'); ?>" class="nav-link <?php echo $uri2 == 'employment' ? 'active' : '' ?>">Status Kepegawaian</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('reference_indicator')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('reference/indicators'); ?>" class="nav-link <?php echo $uri2 == 'indicators' ? 'active' : '' ?>">Indikator</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_system): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['module', 'languages', 'language_field', 'references', 'configuration']) ? 'active' : '' ?>" href="#System" data-bs-toggle="collapse">
                            <i class="bx bx-cog"></i> <span>System</span>
                        </a>
                        <div id="System" class="collapse menu-dropdown <?php echo in_array($uri2, ['module', 'languages', 'language_field', 'references', 'configuration']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('system_module')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('system/module'); ?>" class="nav-link <?php echo $uri2 == 'module' ? 'active' : '' ?>">Module</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('system_language')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('system/languages'); ?>" class="nav-link <?php echo $uri2 == 'languages' ? 'active' : '' ?>">Languages</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('system_language_field')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('system/language_field'); ?>" class="nav-link <?php echo $uri2 == 'language_field' ? 'active' : '' ?>">Language Field</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('system_reference')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('system/references'); ?>" class="nav-link <?php echo $uri2 == 'references' ? 'active' : '' ?>">References</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('system_configuration')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('system/configuration'); ?>" class="nav-link <?php echo $uri2 == 'configuration' ? 'active' : '' ?>">App Configuration</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

                <?php if ($show_log): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['login', 'access', 'change']) ? 'active' : '' ?>" href="#Log" data-bs-toggle="collapse">
                            <i class="bx bx-history"></i> <span>Log</span>
                        </a>
                        <div id="Log" class="collapse menu-dropdown <?php echo in_array($uri2, ['login', 'access', 'change']) ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <?php if ($can_view('log_login')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('log/login'); ?>" class="nav-link <?php echo $uri2 == 'login' ? 'active' : '' ?>">Login</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('log_access')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('log/access'); ?>" class="nav-link <?php echo $uri2 == 'access' ? 'active' : '' ?>">Akses</a></li>
                                <?php endif; ?>
                                <?php if ($can_view('log_change')): ?>
                                    <li class="nav-item"><a href="<?php echo site_url('log/change'); ?>" class="nav-link <?php echo $uri2 == 'change' ? 'active' : '' ?>">Perubahan</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>

            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link menu-link <?php echo $uri1 == 'home' ? 'active' : '' ?>" href="<?php echo site_url('home/dashboard'); ?>">
                        <i class="bx bx-home-circle"></i> <span>Laman</span>
                    </a>
                </li>

                <?php if ($can_view('employee_personnel')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'employee' && $uri2 == 'personnel') ? 'active' : '' ?>" href="<?php echo site_url('employee/personnel'); ?>">
                            <i class="bx bx-group"></i> <span>Data Pegawai</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view('interview_group') || $can_view('interview_participant')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'interview' || $uri1 == 'interview_participant') ? 'active' : '' ?>" href="<?php echo site_url('interview/interview_group/list'); ?>">
                            <i class="bx bxs-user-account"></i> <span>Peserta Wawancara</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view('monitoring_verification')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'monitoring' && $uri2 == 'verification') ? 'active' : '' ?>" href="<?php echo site_url('monitoring/verification'); ?>">
                            <i class="bx bx-check-shield"></i> <span>Monitoring Verifikasi</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view('interview_employee')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo $uri1 == 'interview_employee' ? 'active' : '' ?>" href="<?php echo site_url('interview_employee'); ?>">
                            <i class="bx bx-edit"></i> <span>Isi Wawancara</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($can_view('assessment_scoring')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo ($uri1 == 'assessment' && $uri2 == 'assessment') ? 'active' : '' ?>" href="<?php echo site_url('assessment/assessment'); ?>">
                            <i class="bx bx-star"></i> <span>Penilaian</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($show_rekap): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link <?php echo in_array($uri2, ['recap']) ? 'active' : '' ?>" href="#Rekapitulasi" data-bs-toggle="collapse">
                            <i class="bx bx-folder-open"></i> <span>Rekapitulasi</span>
                        </a>
                        <div id="Rekapitulasi" class="collapse menu-dropdown <?php echo $uri1 == 'recap' ? 'show' : '' ?>">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item"><a href="<?php echo site_url('recap/recap'); ?>" class="nav-link <?php echo $uri1 == 'recap' && $uri2 == 'recap' && $uri3 == '' ? 'active' : '' ?>">Kelompok</a></li>
                                <li class="nav-item"><a href="<?php echo site_url('recap/recap/all'); ?>" class="nav-link <?php echo $uri1 == 'recap' && $uri2 == 'recap' && $uri3 == 'all' ? 'active' : '' ?>">Keseluruhan</a></li>
                            </ul>
                        </div>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

        </ul>
    </div>

    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>