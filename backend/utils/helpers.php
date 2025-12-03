<?php

class Helpers {
    public static function isLoggedInBranch() {
        return isset($_SESSION['branch_id']);
    }

    public static function isLoggedInCustomer() {
        return isset($_SESSION['customer_id']);
    }
}
