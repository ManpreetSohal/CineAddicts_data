<?php

class MovieCompanyAssociation
{
    const QUERY = "INSERT INTO movies_companies_association (movie_id, company_id, role_id) VALUES (:movie_id, :company_id, :role_id)";
    public $movie_id;
    public $company_id;
    public $role_id;
}
