<?php

class ListType
{
    const QUERY = "INSERT INTO list_types (id, type) VALUES (:id, :type)";
    public $id;
    public $type;
}
