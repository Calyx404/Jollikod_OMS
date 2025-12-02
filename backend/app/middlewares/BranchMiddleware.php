<?php

class BranchMiddleware
{
    public static function requireStaffAfterBranch()
    {
        // Branch must be logged in
        AuthMiddleware::branch();

        // Staff must be logged in to access dashboard
        AuthMiddleware::staff();
    }
}
