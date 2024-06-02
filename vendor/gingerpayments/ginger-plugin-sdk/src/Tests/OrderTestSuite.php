<?php

namespace GingerPluginSdk\Tests;

use GingerPluginSdk\Entities\Issuer;
use GingerPluginSdk\Entities\MultiFieldsEntityTraitTest;
use PHPUnit\Framework\TestSuite;

class OrderTestSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new self();
        $suite->addTestSuite(OrderTest::class);
        $suite->addTestSuite(GetOrderTest::class);
        $suite->addTestSuite(AbstractCollectionTest::class);
        $suite->addTestSuite(AdditionalAddressesTest::class);
        $suite->addTestSuite(AddressTest::class);
        $suite->addTestSuite(AmountTest::class);
        $suite->addTestSuite(BaseFieldTest::class);
        $suite->addTestSuite(BirthdateTest::class);
        $suite->addTestSuite(CaptureOrderTransactionTest::class);
        $suite->addTestSuite(ClientOptionsTest::class);
        $suite->addTestSuite(ClientTest::class);
        $suite->addTestSuite(CountryTest::class);
        $suite->addTestSuite(CreateOrderTest::class);
        $suite->addTestSuite(CustomerTest::class);
        $suite->addTestSuite(EmailTest::class);
        $suite->addTestSuite(EventsTest::class);
        $suite->addTestSuite(EventTest::class);
        $suite->addTestSuite(ExtraTest::class);
        $suite->addTestSuite(FromArrayTest::class);
        $suite->addTestSuite(GetIdealIssuersTest::class);
        $suite->addTestSuite(IdealIssuersTest::class);
        $suite->addTestSuite(IssuerTest::class);
        $suite->addTestSuite(LineTest::class);
        $suite->addTestSuite(LocaleTest::class);
        $suite->addTestSuite(MultiCurrencyTest::class);
        $suite->addTestSuite(OrderLinesTest::class);
        $suite->addTestSuite(PaymentMethodDetailsTest::class);
        $suite->addTestSuite(PhoneNumbersTest::class);
        $suite->addTestSuite(RawCostTest::class);
        $suite->addTestSuite(RefundOrderTest::class);
        $suite->addTestSuite(StatusTest::class);
        $suite->addTestSuite(ToCamelFromCamelTest::class);
        $suite->addTestSuite(TransactionsTest::class);
        $suite->addTestSuite(TransactionTest::class);
        $suite->addTestSuite(VatPercentageTest::class);
        $suite->addTestSuite(SingleFieldTraitTest::class);
        $suite->addTestSuite(UpdateOrderTest::class);
        return $suite;
    }
}

?>