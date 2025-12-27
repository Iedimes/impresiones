<?php

namespace App\Database;

use Illuminate\Database\Query\Grammars\SqlServerGrammar;
use Illuminate\Database\Query\Builder;

class LegacySqlServerGrammar extends SqlServerGrammar
{
    /**
     * Compile a select query into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        if (!$query->offset) {
            return parent::compileSelect($query);
        }

        // If an offset is present, we basically need to implement standard Laravel 5-8 behavior
        // wrapping the query to use ROW_NUMBER().

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        $components = $this->compileComponents($query);

        // Get the order by for ROW_NUMBER
        $order = $this->compileOrders($query, $query->orders);
        if (empty($query->orders)) {
            $order = 'ORDER BY (SELECT 0)';
        }

        // Remove orders from inner component list to avoid double ordering inside the over() and outside
        unset($components['orders']);
        unset($components['offset']);
        unset($components['limit']);

        // Generate the inner SQL (select ... from ... where ...)
        $inner = $this->concatenate($components);

        // Current Offset and Limit
        $offset = (int) $query->offset;
        $limit  = (int) $query->limit;

        // Calculate ROW_NUMBER range
        $start = $offset + 1;
        $end   = $offset + $limit;

        // We need to inject ROW_NUMBER() into the select list of the inner query.
        // But simply appending to $inner string is hard.
        // We need to modify the 'columns' component BEFORE concatenation if possible,
        // OR wrap the entire inner query as a subquery.

        // Strategy:
        // SELECT * FROM (
        //    SELECT *, ROW_NUMBER() OVER ($order) AS row_num FROM ( $inner_without_select_keyword? )
        // ) AS temp WHERE row_num BETWEEN $start AND $end

        // EASIER STRATEGY (like old Laravel):
        // Modify the SELECT clause to Include ROW_NUMBER()

        $selects = $this->compileColumns($query, $query->columns);

        // Rebuild inner query with ROW_NUMBER injected
        // We can't rely on $components['columns'] easily because it's a string.
        // Let's assume we can reconstruct it.

        $components['columns'] = $selects . ", ROW_NUMBER() OVER ($order) AS row_num";

        $innerSql = $this->concatenate($components);

        return "SELECT * FROM ({$innerSql}) AS laravel_chk_alias WHERE row_num BETWEEN {$start} AND {$end}";
    }
}
