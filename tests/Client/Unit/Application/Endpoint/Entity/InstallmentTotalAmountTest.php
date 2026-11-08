<?php

namespace Alma\Client\Tests\Unit\Application\Endpoint\Entity;

use Alma\Client\Domain\Entity\Installment;
use PHPUnit\Framework\TestCase;

/**
 * The total amount of an installment: Alma's total_amount when Alma sends it, the sum of the purchase
 * amount, the customer fee and the customer interest otherwise; the backend is the master of the data.
 */
class InstallmentTotalAmountTest extends TestCase
{
    public function testTheTotalAmountIsTheOneAlmaSendsEvenWhenItDiffersFromTheSum()
    {
        $installment = new Installment([
            'purchase_amount' => 6668,
            'customer_fee' => 310,
            'customer_interest' => 0,
            'due_date' => 1636386621,
            'total_amount' => 6978,
            'state' => Installment::STATE_PENDING,
        ]);
        $this->assertSame(6978, $installment->getTotalAmount());
        $this->assertSame(6978, $installment->getComputedTotalAmount());
        $this->assertTrue($installment->hasConsistentTotalAmount());

        $inconsistent = new Installment([
            'purchase_amount' => 6668,
            'customer_fee' => 310,
            'customer_interest' => 0,
            'due_date' => 1636386621,
            'total_amount' => 7000,
            'state' => Installment::STATE_PENDING,
        ]);
        $this->assertSame(7000, $inconsistent->getTotalAmount(), "Alma's value is shown, not the sum");
        $this->assertSame(6978, $inconsistent->getComputedTotalAmount());
        $this->assertFalse($inconsistent->hasConsistentTotalAmount());
    }

    public function testTheTotalAmountIsComputedWhenAlmaSendsNone()
    {
        $installment = new Installment([
            'purchase_amount' => 15829,
            'customer_fee' => 816,
            'customer_interest' => 5,
            'due_date' => 1757407747,
            'state' => Installment::STATE_PAID,
        ]);
        $this->assertSame(16650, $installment->getTotalAmount());
        $this->assertTrue($installment->hasConsistentTotalAmount());
    }

    public function testNumericStringsAreTypedAsTheOtherAmountsAre()
    {
        $installment = new Installment([
            'purchase_amount' => '2286',
            'customer_fee' => '0',
            'customer_interest' => 0,
            'due_date' => 1762473600,
            'total_amount' => '2286',
            'state' => Installment::STATE_FORECAST,
        ]);
        $this->assertSame(2286, $installment->getTotalAmount());
        $this->assertTrue($installment->hasConsistentTotalAmount());
    }
}
