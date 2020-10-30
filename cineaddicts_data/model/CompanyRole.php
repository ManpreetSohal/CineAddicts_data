<?php

class CompanyRole
{
    const QUERY = "INSERT INTO company_roles (wiki_property, company_role) VALUES (:wiki_property, :company_role)";
    public $wiki_property;
    public $company_role;
}
