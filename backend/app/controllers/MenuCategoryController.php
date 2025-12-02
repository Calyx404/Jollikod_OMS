<?php

class MenuCategoryController extends BaseController
{
    private MenuCategory $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new MenuCategory();
    }

    // POST /api/menu-category/create
    public function create(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();

        $data = $req->body();
        $errors = Validator::required(['name'], $data);

        if (!empty($errors)) {
            return $res->json(['status' => 422, 'errors' => $errors], 422);
        }

        $category_id = $this->model->create(Sanitizer::cleanString($data['name']));
        return $res->json(['status' => 201, 'message' => 'Category created', 'category_id' => $category_id]);
    }

    // GET /api/menu-category/list
    public function list(Request $req, Response $res)
    {
        BranchMiddleware::requireStaffAfterBranch();
        $categories = $this->model->all();
        return $res->json(['status' => 200, 'categories' => $categories]);
    }
}
