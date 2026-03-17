<?php

namespace CorreoArgentino\Interfaces;

interface CorreoArgentinoServicePaqArInterface extends CorreoArgentinoServiceInterface
{
    public function login($agreement, $apiKey, $mode);
    public function cancel($tracking);
    public function label($tracking);
    public function getRates($postalCode, $deliveryType, $dimensions);

    /**
     * Get all branches from Correo Argentino API using iso_state
     */
    public function getBranches(string $iso_state): array;

    /**
     * Get branch data from Correo Argentino API using iso_state and filtering using branch_id.
     * Correo Argentino API does not provides an endpoint to filter by branch_id 
     */
    public function getBranchData(string $branch_id, string $iso_state): array;

    /**
     * Get branch name from branch data
     */
    public function getBranchName(array $branch): string;
}
