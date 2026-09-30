<?php

namespace App\Utilities;

class TaxService
{
    public static function calculateTax(int $gross, int $tax_percent, int $profit, int $round_up = 5000)
    {
        $taxOnGross = $gross * ($tax_percent / 100);
        $taxedGross = $gross + $taxOnGross;
        $roundedGross = ceil($taxedGross / $round_up) * $round_up;
        $roundUp = $roundedGross - $taxedGross;
        $taxRateForRoundUp = 1 + ($tax_percent / 100);
        $profitOnRoundUp = $roundUp / $taxRateForRoundUp;
        $taxOnRoundUp = $roundUp - $profitOnRoundUp;
        $totalProfit = $profit + $profitOnRoundUp;
        $totalTax = $taxOnGross + $taxOnRoundUp;
        $totalPrice = $gross + $totalProfit + $totalTax;

        return [
            'round_up' => $roundUp,
            'total_tax' => $totalTax,
            'total_price' => $totalPrice,
            'total_profit' => $totalProfit
        ];
    }
}
