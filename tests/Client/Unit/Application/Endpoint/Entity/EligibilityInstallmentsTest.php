<?php

namespace Alma\Client\Tests\Unit\Application\Endpoint\Entity;

use Alma\Client\Application\Exception\ParametersException;
use Alma\Client\Domain\Entity\Eligibility;
use Alma\Client\Domain\Entity\Installment;
use Alma\Client\Domain\Entity\PaymentPlan;
use PHPUnit\Framework\TestCase;

/**
 * The payment plan of an eligibility typed as a Payment's: the rows Alma sends for a forecast schedule,
 * which carry no state, become Installments in the state forecast; a state Alma sent is kept.
 */
class EligibilityInstallmentsTest extends TestCase
{
    private function eligible(array $paymentPlan): Eligibility
    {
        return new Eligibility([
            'eligible' => true,
            'deferred_days' => 0,
            'deferred_months' => 0,
            'installments_count' => count($paymentPlan),
            'customer_fee' => 310,
            'customer_total_cost_amount' => 310,
            'customer_total_cost_bps' => 155,
            'annual_interest_rate' => 0,
            'payment_plan' => $paymentPlan,
        ]);
    }

    public function testTheRowsOfAForecastScheduleBecomeInstallmentsInTheStateForecast()
    {
        $eligibility = $this->eligible([
            ['due_date' => 1636386621, 'purchase_amount' => 6668, 'customer_fee' => 310, 'customer_interest' => 0, 'total_amount' => 6978, 'localized_due_date' => "aujourd'hui", 'time_delta_from_start' => null],
            ['due_date' => 1638978621, 'purchase_amount' => '6666', 'customer_fee' => 0, 'customer_interest' => 0, 'total_amount' => 6666],
        ]);

        $plan = $eligibility->getInstallments();

        $this->assertInstanceOf(PaymentPlan::class, $plan);
        $this->assertSame(2, $plan->getInstallmentCount());
        $first = $plan[0];
        $this->assertInstanceOf(Installment::class, $first);
        $this->assertSame(Installment::STATE_FORECAST, $first->getState());
        $this->assertSame(6978, $first->getTotalAmount());
        $this->assertSame(1636386621, $first->getDueDate());
        $this->assertSame(6666, $plan[1]->getPurchaseAmount(), 'typed by the entity, as the rows of a Payment are');
        $this->assertSame(6978, $eligibility->getPaymentPlan()[0]['total_amount'], 'the raw rows stay available');
    }

    public function testAStateAlmaSentIsKept()
    {
        $eligibility = $this->eligible([
            ['due_date' => 1636386621, 'purchase_amount' => 6668, 'customer_fee' => 310, 'customer_interest' => 0, 'total_amount' => 6978, 'state' => Installment::STATE_PENDING],
        ]);

        $this->assertSame(Installment::STATE_PENDING, $eligibility->getInstallments()[0]->getState());
    }

    public function testARowMissingARequiredFieldThrowsAsForAPayment()
    {
        $eligibility = $this->eligible([
            ['due_date' => 1636386621, 'purchase_amount' => 6668, 'customer_fee' => 310, 'customer_interest' => 0, 'total_amount' => 6978],
            ['due_date' => 1638978621, 'customer_fee' => 0, 'customer_interest' => 0, 'total_amount' => 6666],
        ]);

        $this->expectException(ParametersException::class);
        $eligibility->getInstallments();
    }
}
