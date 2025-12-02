<?php

class BranchOrderController extends BaseController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private OrderLog $orderLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->orderLogModel = new OrderLog();
    }

    // GET /api/branch/orders?status=placed
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $status = $req->query('status', '');
        $branch_id = $_SESSION['branch_id'];

        $orders = $this->orderModel->listByBranch($branch_id, $status);
        foreach ($orders as &$order) {
            $order['items'] = $this->orderItemModel->listByOrder($order['order_id']);
        }

        return $res->json(['status' => 200, 'orders' => $orders]);
    }

    // POST /api/branch/order/update-status
    public function updateStatus(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['order_id', 'status'], $data);

        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $order = $this->orderModel->find((int)$data['order_id']);
        if (!$order || $order['branch_id'] != $_SESSION['branch_id']) {
            return $res->json(['status' => 404, 'message' => 'Order not found'], 404);
        }

        $validStatuses = ['queued', 'preparing', 'delivering', 'delivered', 'received'];
        if (!in_array($data['status'], $validStatuses)) {
            return $res->json(['status' => 400, 'message' => 'Invalid status'], 400);
        }

        // Update order status
        $this->orderModel->updateStatus($order['order_id'], $data['status']);

        // Log the status timestamp
        $this->orderLogModel->create($order['order_id'], $data['status']);

        return $res->json(['status' => 200, 'message' => 'Order status updated']);
    }
}
