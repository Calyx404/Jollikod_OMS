<?php

class BranchAnalyticsController extends BaseController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private Payment $paymentModel;
    private Customer $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->paymentModel = new Payment();
        $this->customerModel = new Customer();
    }

    // GET /api/branch/analytics/sales-overview?from=2025-12-01&to=2025-12-02
    public function salesOverview(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $stats = $this->paymentModel->getTotalRevenue($branch_id, $from, $to);
        $totalRevenue = $stats['total_revenue'];
        $totalOrders = $stats['total_orders'];
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        return $res->json([
            'status' => 200,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order_value' => $avgOrderValue
        ]);
    }

    // GET /api/branch/analytics/top-items?from=2025-12-01&to=2025-12-02
    public function topItems(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $topItems = $this->orderItemModel->getTopItems($branch_id, $from, $to);

        return $res->json(['status' => 200, 'top_items' => $topItems]);
    }

    // GET /api/branch/analytics/customer-retention?from=2025-12-01&to=2025-12-02
    public function customerRetention(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $stats = $this->customerModel->getRetentionStats($branch_id, $from, $to);
        $totalCustomers = $stats['total_customers'];
        $returningCustomers = $stats['returning_customers'];
        $newCustomers = $totalCustomers - $returningCustomers;
        $retentionRate = $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 2) : 0;

        return $res->json([
            'status' => 200,
            'total_customers' => $totalCustomers,
            'returning_customers' => $returningCustomers,
            'new_customers' => $newCustomers,
            'retention_rate' => $retentionRate
        ]);
    }

    // Additional analytics endpoints can follow the same structure (demographics, sales logs, CSV export, etc.)
}
