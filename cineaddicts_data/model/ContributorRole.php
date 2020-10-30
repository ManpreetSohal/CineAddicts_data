<?php

class ContributorRole
{
    const QUERY = "INSERT INTO contributor_roles (wiki_property, contributor_role) VALUES (:wiki_property, :contributor_role)";  
    public $wiki_property;
    public $contributor_role;
}
