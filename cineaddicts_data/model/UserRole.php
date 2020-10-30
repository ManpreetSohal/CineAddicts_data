<?php

class UserRole
{
    const QUERY = "INSERT INTO user_roles (id, role) VALUES (:id, :role)";
    public $id;
    public $role;
}
