<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait Filterable
{
    /**
     * Aplica filtros baseados na request.
     * Espera que cada controller defina o método `filterableFields()`.
     */
    // public function applyFilters($query, Request $request)
    // {
    //     foreach ($this->filterableFields() as $field) {
    //         if ($request->filled($field)) {
    //             $query->where($field, $request->$field);
    //         }
    //     }

    //     return $query;
    // }
     protected function applyFilters($query, Request $request)
    {
        $fields = $this->filterableFields();

        foreach ($fields as $field) {
            $value = $request->input($field);
            if (!is_null($value) && $value !== '') {
                $query->where($field, 'LIKE', "%{$value}%");
            }
        }

        return $query;
    }
    /**
     * Deve ser sobrescrito no controller que usa o trait.
     */
    protected function filterableFields(): array
    {
        return [];
    }
}
