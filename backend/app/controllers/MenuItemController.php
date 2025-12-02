<?php

class MenuItemController extends BaseController
{
    private MenuItem $menuItemModel;
    private MenuInventory $inventoryModel;
    private Menu $menuModel;

    public function __construct()
    {
        parent::__construct();
        $this->menuItemModel = new MenuItem();
        $this->inventoryModel = new MenuInventory();
        $this->menuModel = new Menu();
    }

    // POST /api/menu-item/create
    public function create(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['menu_id', 'menu_category_id', 'name', 'price'], $data);

        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $data['name'] = Sanitizer::cleanString($data['name']);
        $data['description'] = $data['description'] ?? '';
        $data['price'] = (float)$data['price'];
        $data['image_path'] = $data['image_path'] ?? '';

        $item_id = $this->menuItemModel->create($data);

        // Create inventory record automatically
        $this->inventoryModel->create((int)$data['menu_id'], $item_id, $data['stock_quantity'] ?? 0);

        return $res->json(['status' => 201, 'message' => 'Menu item created', 'menu_item_id' => $item_id]);
    }

    // GET /api/menu-item/list?menu_id=1
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $menu_id = (int)$req->query('menu_id', 0);
        if ($menu_id <= 0) {
            return $res->json(['status' => 400, 'message' => 'menu_id is required'], 400);
        }

        $items = $this->inventoryModel->getByMenu($menu_id);

        return $res->json(['status' => 200, 'items' => $items]);
    }
}
