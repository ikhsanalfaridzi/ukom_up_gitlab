<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn" id="vertical-menu-btn">
                <i class="bx bx-menu"></i>
            </button>
            <img src="<?php echo base_url('vlz/images/logo-imipas.png') ?>" alt="IMIPAS" style="height: 56px; margin-left: 80px; object-fit: contain;">
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                    <i class="bx bx-fullscreen fs-22"></i>
                </button>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                    <i class="bx bx-moon fs-22"></i>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="topbar-head-dropdown dropdown">
                    <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img class="rounded-circle header-profile-user" src="<?php echo base_url('vlz/images/users/no-photo.jpg') ?>" alt="User" style="width: 32px; height: 32px; object-fit: cover;">
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-1"><?php echo kv_decrypt(session()->get('login_username'), getenv('APP_KEY')) ?></h6>
                            <p class="mb-0 text-muted fs-12"><?php echo implode(', ', session()->get('login_user_group') ?? []) ?></p>
                        </div>
                        <a class="dropdown-item" href="<?php echo site_url('authentication/logout') ?>">
                            <i class="bx bx-power-off me-2 text-muted"></i>
                            <span>Log Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
