<template>
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index3.html" class="brand-link">
            <img
                src="dist/img/AdminLTELogo.png"
                alt="AdminLTE Logo"
                class="brand-image img-circle elevation-3"
                style="opacity: .8"
            />
            <span class="brand-text font-weight-light h6">ERP Procurement System</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul
                    class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false"
                >
                    <li class="nav-header">WORKFLOW CONTROLLER</li>
                    <li class="nav-item">
                        <router-link to="/participants" class="nav-link">
                            <i class="nav-icon far fa-circle text-danger"></i>
                            <p class="text">Participants</p>
                        </router-link>                 </li>
                    <li class="nav-item">
                        <router-link to="/inputs" class="nav-link">
                            <i class="nav-icon far fa-circle text-warning"></i>
                            <p>Inputs</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/approval" class="nav-link">
                            <i class="nav-icon far fa-circle text-info"></i>
                            <p>Approval</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/inprogress" class="nav-link">
                            <i class="nav-icon far fa-circle text-info"></i>
                            <p>In Progress</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/clarification" class="nav-link">
                            <i class="nav-icon far fa-circle text-info"></i>
                            <p>Clarification</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/approved" class="nav-link">
                            <i class="nav-icon far fa-circle text-info"></i>
                            <p>Approved</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/invalid" class="nav-link">
                            <i class="nav-icon far fa-circle text-info"></i>
                            <p>Invalid</p>
                        </router-link>
                    </li>
                    <li class="nav-item">
                        <router-link to="/rejected" class="nav-link">
                            <i class="nav-icon far fa-circle text-info"></i>
                            <p>Rejected</p>
                        </router-link>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
</template>
