<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <img src="https://via.placeholder.com/33x33/007bff/ffffff?text=S66" alt="Shop66 Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Shop66 Ledger</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="https://via.placeholder.com/160x160/007bff/ffffff?text={{ strtoupper(substr(session('mock_user.name', 'U'), 0, 1)) }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="{{ route('profile.show') }}" class="d-block">{{ session('mock_user.name', 'Guest User') }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Transactions -->
                <li class="nav-item {{ request()->routeIs('transactions.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p>
                            Transactions
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Transactions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('transactions.create') }}" class="nav-link {{ request()->routeIs('transactions.create') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Transaction</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Documents -->
                <li class="nav-item {{ request()->routeIs('documents.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>
                            Documents
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Documents</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('documents.upload') }}" class="nav-link {{ request()->routeIs('documents.upload') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Upload Documents</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('documents.review') }}" class="nav-link {{ request()->routeIs('documents.review') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Pending Review</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Master Data -->
                <li class="nav-item {{ request()->routeIs(['vendors.*', 'customers.*', 'items.*', 'categories.*', 'accounts.*']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs(['vendors.*', 'customers.*', 'items.*', 'categories.*', 'accounts.*']) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            Master Data
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('vendors.index') }}" class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Vendors</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Customers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Items</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Categories</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('accounts.index') }}" class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Accounts</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Reports
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('reports.income-statement') }}" class="nav-link {{ request()->routeIs('reports.income-statement') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Income Statement</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.expense-report') }}" class="nav-link {{ request()->routeIs('reports.expense-report') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Expense Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('reports.vendor-ledger') }}" class="nav-link {{ request()->routeIs('reports.vendor-ledger') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Vendor Ledger</p>
                            </a>
                        </li>
                    </ul>
                </li>

                @if(session('mock_user.role') === 'admin')
                <!-- Administration -->
                <li class="nav-header">ADMINISTRATION</li>
                <li class="nav-item {{ request()->routeIs(['stores.*', 'users.*']) ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs(['stores.*', 'users.*']) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Administration
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('stores.index') }}" class="nav-link {{ request()->routeIs('stores.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stores</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Users</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>