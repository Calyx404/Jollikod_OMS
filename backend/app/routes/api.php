<?php

/** @var Router $app->router() */

/* AUTHENTICATION ROUTES */

// Customer Auth
$app->router()->register('POST', '/api/auth/customer/register', [AuthController::class, 'registerCustomer']);
$app->router()->register('POST', '/api/auth/customer/login', [AuthController::class, 'loginCustomer']);
$app->router()->register('POST', '/api/auth/customer/logout', [AuthController::class, 'logoutCustomer']);

// Branch Auth
$app->router()->register('POST', '/api/auth/branch/register', [AuthController::class, 'registerBranch']);
$app->router()->register('POST', '/api/auth/branch/login', [AuthController::class, 'loginBranch']);
$app->router()->register('POST', '/api/auth/branch/logout', [AuthController::class, 'logoutBranch']);

// Staff Auth
$app->router()->register('POST', '/api/auth/staff/login', [StaffController::class, 'loginStaff']);
$app->router()->register('POST', '/api/auth/staff/logout', [StaffController::class, 'logoutStaff']);

/* MENU ROUTES */

// Menu
$app->router()->register('POST', '/api/menu/create', [MenuController::class, 'create']);
$app->router()->register('GET', '/api/menu/list', [MenuController::class, 'list']);

// Menu Category
$app->router()->register('POST', '/api/menu-category/create', [MenuCategoryController::class, 'create']);
$app->router()->register('GET', '/api/menu-category/list', [MenuCategoryController::class, 'list']);

// Menu Items
$app->router()->register('POST', '/api/menu-item/create', [MenuItemController::class, 'create']);
$app->router()->register('GET', '/api/menu-item/list', [MenuItemController::class, 'list']);

// Menu Item Schedule
$app->router()->register('POST', '/api/menu-item/schedule/create', [MenuItemScheduleController::class, 'create']);
$app->router()->register('GET',  '/api/menu-item/schedule/list',   [MenuItemScheduleController::class, 'list']);
$app->router()->register('POST', '/api/menu-item/schedule/delete', [MenuItemScheduleController::class, 'delete']);

// Menu Item Availability
$app->router()->register('POST', '/api/menu-item/toggle', [MenuItemAvailabilityController::class, 'toggle']);

/* ORDER ROUTES */

// Order
$app->router()->register('POST', '/api/order/create', [OrderController::class, 'create']);
$app->router()->register('GET', '/api/order/list', [OrderController::class, 'list']);

/* BRANCH ROUTES */

// Branch Staff
$app->router()->register('POST', '/api/branch/staff/create', [StaffController::class, 'create']);
$app->router()->register('PUT',  '/api/branch/staff/update', [StaffController::class, 'update']);
$app->router()->register('GET',  '/api/branch/staff/list',   [StaffController::class, 'list']);
$app->router()->register('POST', '/api/branch/staff/deactivate', [StaffController::class, 'deactivate']);
$app->router()->register('POST', '/api/branch/staff/activate', [StaffController::class, 'activate']);
$app->router()->register('POST', '/api/branch/staff/change-role', [StaffController::class, 'changeRole']);

// Branch Menu
$app->router()->register('POST', '/api/branch/inventory/adjust', [InventoryController::class, 'adjust']);
$app->router()->register('GET',  '/api/branch/inventory/list',   [InventoryController::class, 'list']);
$app->router()->register('GET',  '/api/branch/menu-logs',        [MenuLogController::class, 'list']);

// Branch Orders
$app->router()->register('GET', '/api/branch/orders', [BranchOrderController::class, 'list']);
$app->router()->register('POST', '/api/branch/order/update-status', [BranchOrderController::class, 'updateStatus']);

// Branch Activities
$app->router()->register('GET',  '/api/branch/rankings/items',   [RankingsController::class, 'items']);
$app->router()->register('GET', '/api/branch/activity/orders', [BranchActivityController::class, 'getOrdersByStatus']);

// Branch Analytics
$app->router()->register('GET', '/api/branch/analytics/sales-overview', [BranchAnalyticsController::class, 'salesOverview']);
$app->router()->register('GET', '/api/branch/analytics/top-items', [BranchAnalyticsController::class, 'topItems']);
$app->router()->register('GET', '/api/branch/analytics/customer-retention', [BranchAnalyticsController::class, 'customerRetention']);
$app->router()->register('GET', '/api/branch/analytics/customer-demographics', [CustomerAnalyticsController::class, 'customerDemographics']);
$app->router()->register('GET', '/api/branch/analytics/feedback-summary', [CustomerAnalyticsController::class, 'feedbackSummary']);

// Branch Analytics Export
$app->router()->register('GET', '/api/branch/analytics/sales-log/export', [BranchAnalyticsExportController::class, 'exportSalesLog']);
$app->router()->register('GET', '/api/branch/analytics/customer-log/export', [BranchAnalyticsExportController::class, 'exportCustomerLog']);
$app->router()->register('GET', '/api/branch/analytics/inventory-log/export', [MenuInventoryExportController::class, 'exportInventoryLog']);
$app->router()->register('GET', '/api/branch/analytics/inventory-status', [MenuInventoryExportController::class, 'inventoryStatus']);

/* CUSTOMER ROUTES */

// Customer
$app->router()->register('GET', '/api/customer/menu', [CustomerController::class, 'menu']);
$app->router()->register('POST', '/api/customer/order', [CustomerController::class, 'placeOrder']);
$app->router()->register('GET', '/api/customer/orders', [CustomerController::class, 'orders']);