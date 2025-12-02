<?php

class CustomerController extends BaseController
{
    private Customer $customerModel;
    private MenuItem $menuItemModel;
    private MenuInventory $inventoryModel;
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private Payment $paymentModel;
    private OrderLog $orderLogModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->menuItemModel = new MenuItem();
        $this->inventoryModel = new MenuInventory();
        $this->orderModel = new Order();
        $this->orderItemModel = new OrderItem();
        $this->paymentModel = new Payment();
        $this->orderLogModel = new OrderLog();
    }

    // GET /api/customer/menu?branch_id=1
    public function menu(Request $req, Response $res)
    {
        $branch_id = (int)$req->query('branch_id', 0);
        if ($branch_id <= 0) {
            return $res->json(['status' => 400, 'message' => 'Invalid branch']);
        }

        $items = $this->menuItemModel->getAvailableByBranch($branch_id, $req->query('datetime') ?? null);

        foreach ($items as &$item) {
            $item['stock'] = $this->inventoryModel->getStock($item['menu_item_id']);
        }

        return $res->json(['status' => 200, 'menu_items' => $items]);
    }

    // POST /api/customer/order
    public function placeOrder(Request $req, Response $res)
    {
        $data = $req->body();
        $errors = Validator::required(['customer_id', 'branch_id', 'destination_address', 'cart'], $data);

        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $customer_id = (int)$data['customer_id'];
        $branch_id = (int)$data['branch_id'];
        $destination_address = $data['destination_address'];
        $cart = $data['cart']; // array of {menu_item_id, quantity}

        // Create order
        $order_id = $this->orderModel->create([$branch_id, $customer_id, $destination_address, count($cart)]);

        $subtotal = 0;

        foreach ($cart as $item) {
            $menu_item_id = (int)$item['menu_item_id'];
            $quantity = (int)$item['quantity'];
            $unit_price = $this->menuItemModel->getPrice($menu_item_id);
            $total = $unit_price * $quantity;
            $subtotal += $total;

            // Add to order_items
            $this->orderItemModel->create($order_id, $menu_item_id, $quantity, $unit_price, $total);

            // Reduce inventory
            $currentStock = $this->inventoryModel->getStock($menu_item_id);
            $this->inventoryModel->updateStock($menu_item_id, max($currentStock - $quantity, 0));
        }

        // Create payment (simulated)
        $vat = round($subtotal * 0.12, 2);
        $delivery_fee = 50; // fixed for MVP
        $total = $subtotal + $vat + $delivery_fee;
        $this->paymentModel->create($order_id, $subtotal, $vat, $delivery_fee, $total);

        // Log order
        $this->orderLogModel->create($order_id, 'placed');

        return $res->json(['status' => 200, 'message' => 'Order placed successfully', 'order_id' => $order_id]);
    }

    // GET /api/customer/orders?customer_id=1
    public function orders(Request $req, Response $res)
    {
        $customer_id = (int)$req->query('customer_id', 0);
        if ($customer_id <= 0) {
            return $res->json(['status' => 400, 'message' => 'Invalid customer']);
        }

        $orders = $this->orderModel->listByCustomer($customer_id);

        foreach ($orders as &$order) {
            $order['items'] = $this->orderItemModel->listByOrder($order['order_id']);
            $order['payment'] = $this->paymentModel->getByOrder($order['order_id']);
            $order['logs'] = $this->orderLogModel->listByOrder($order['order_id']);
        }

        return $res->json(['status' => 200, 'orders' => $orders]);
    }
}
