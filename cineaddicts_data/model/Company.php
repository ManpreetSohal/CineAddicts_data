<?php

class Company
{
    const QUERY = "INSERT INTO companies (wiki_id, company) VALUES (:wiki_id, :company)";
    public $wiki_id;
    public $company;
}
