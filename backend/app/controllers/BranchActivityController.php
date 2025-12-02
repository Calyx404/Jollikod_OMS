<?php

class BranchActivityController extends BaseController
{
    private Order $orderModel;
    private OrderLog $orderLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderLogModel = new OrderLog();
    }

    // GET /api/branch/activity/orders?status=placed&from=2025-12-01&to=2025-12-02
    public function getOrdersByStatus(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $branch_id = $_SESSION['branch_id'];
        $status = $req->query('status', 'placed');
        $from = $req->query('from', date('Y-m-d'));
        $to = $req->query('to', date('Y-m-d'));

        $orders = $this->orderModel->getOrdersByStatus($branch_id, $status, $from, $to);

        return $res->json([
            'status' => 200,
            'orders' => $orders
        ]);
    }
}
