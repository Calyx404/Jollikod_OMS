<?php

class InventoryController extends BaseController
{
    private MenuInventory $inventoryModel;
    private MenuItem $menuItemModel;

    public function __construct()
    {
        parent::__construct();
        $this->inventoryModel = new MenuInventory();
        $this->menuItemModel = new MenuItem();
    }

    // POST /api/branch/inventory/adjust
    // payload: { menu_item_id, delta }
    public function adjust(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['menu_item_id', 'delta'], $data);
        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $menu_item_id = (int)$data['menu_item_id'];
        $delta = (int)$data['delta'];
        $staff_id = $_SESSION['staff_id'] ?? null;

        // Ensure the menu_item belongs to the branch
        $items = $this->menuItemModel->getByBranch($_SESSION['branch_id']);
        $belongs = false;
        foreach ($items as $it) {
            if ((int)$it['menu_item_id'] === $menu_item_id) { $belongs = true; break; }
        }
        if (!$belongs) {
            return $res->json(['status' => 403, 'message' => 'Item not found in this branch'], 403);
        }

        try {
            $newQty = $this->inventoryModel->adjustStock($menu_item_id, $delta, $staff_id);
            return $res->json(['status' => 200, 'menu_item_id' => $menu_item_id, 'new_quantity' => $newQty]);
        } catch (Exception $e) {
            return $res->json(['status' => 500, 'message' => 'Failed to adjust stock', 'error' => $e->getMessage()], 500);
        }
    }

    // GET /api/branch/inventory/list?menu_id=1
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $menu_id = (int)$req->query('menu_id', 0);
        if ($menu_id <= 0) {
            return $res->json(['status' => 400, 'message' => 'menu_id required'], 400);
        }

        $rows = $this->inventoryModel->getByMenu($menu_id);
        return $res->json(['status' => 200, 'items' => $rows]);
    }
}
