<?php

class OrderController extends BaseController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private MenuInventory $inventoryModel;
    private OrderLog $orderLogModel;
    private Payment $paymentModel;

    public function __construct()
    {
        parent::__construct();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->inventoryModel = new MenuInventory();
        $this->orderLogModel = new OrderLog();
        $this->paymentModel = new Payment();
    }

    // POST /api/order/create
    public function create(Request $req, Response $res)
    {
        AuthMiddleware::customer();

        $data = $req->body();
        $errors = Validator::required(['branch_id', 'items', 'store_address', 'destination_address'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $items = $data['items']; // [{menu_item_id, quantity, unit_price}, ...]

        $total_quantity = array_sum(array_column($items, 'quantity'));

        $order_id = $this->orderModel->create([
            'branch_id' => $data['branch_id'],
            'customer_id' => $_SESSION['customer_id'],
            'item_quantity' => $total_quantity,
            'store_address' => $data['store_address'],
            'destination_address' => $data['destination_address'],
            'status' => 'placed'
        ]);

        // Insert order items & deduct inventory
        foreach ($items as $item) {
            $this->orderItemModel->create($order_id, $item['menu_item_id'], $item['quantity'], $item['unit_price'], 0.0);
            $currentStock = $this->inventoryModel->getStock($item['menu_item_id']);
            $newStock = $currentStock - $item['quantity'];
            $this->inventoryModel->updateStock($item['menu_item_id'], max($newStock, 0));

        }

        // Log placed status
        $this->orderLogModel->create($order_id, 'placed');

        // Calculate payment
        $subtotal = array_sum(array_map(fn($i)=> $i['quantity'] * $i['unit_price'], $items));
        $vat = $subtotal * 0.12;
        $delivery_fee = 50; // flat fee
        $total = $subtotal + $vat + $delivery_fee;

        $payment_id = $this->paymentModel->create($order_id, $subtotal, $vat, $delivery_fee, $total);

        return $res->json([
            'status' => 201,
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'payment_id' => $payment_id,
            'total' => $total
        ]);
    }

    // GET /api/order/list
    public function list(Request $req, Response $res)
    {
        AuthMiddleware::customer();
        $orders = $this->orderModel->listByCustomer($_SESSION['customer_id']);
        return $res->json(['status' => 200, 'orders' => $orders]);
    }
}
