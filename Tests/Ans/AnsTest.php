<?php

namespace Tests\Ans;

use App\Endpoint\Ans\Ans\Event;
use App\Models\Avatar;
use App\Models\Set\MessageSet;
use App\Models\Set\TransactionsSet;
use Tests\Control\CouponsTest;
use Tests\Control\SlconfigTest;
use Tests\TestWorker;

class AnsTest extends TestWorker
{
    public function test_Event()
    {
        global $system;
        $couponMake = new CouponsTest();
        $couponMake->test_Create();
        $SlconfigTest = new SlconfigTest();
        $SlconfigTest->test_Update();

        $_SERVER["HTTP_X_ANS_VERIFY_HASH"] = sha1("yep".$system->getSlConfig()->getAnsSalt());
        $_SERVER["QUERY_STRING"] = "yep";
        $_GET["PayerName"] = "James Pond";
        $_GET["PayerKey"] = "289c3e36-69b3-40c5-9229-0c6a5d230767";
        $_GET["ReceiverName"] = "Madpeter Zond";
        $_GET["ReceiverKey"] = "289c3e36-69b3-40c5-9229-0c6a5d230766";
        $_GET["TransactionID"] = "yep";
        $_GET["ItemID"] = "54321";
        $_GET["Type"] = "Purchase";
        $_GET["PaymentGross"] = "100";

        $avatar = new Avatar();
        $avatar->loadId(2);
        $this->assertSame(0, $avatar->getCredits(), "Expected credit level is wrong");

        $messages = new MessageSet();
        $this->assertSame(0,$messages->countInDB()->items,"Incorrect number of messages in outbox");

        $transactions = new TransactionsSet();
        $this->assertSame(0,$transactions->countInDB()->items,"Incorrect number of transactions");
        
        $ansEvent = new Event();
        $ansEvent->process();
        $reply = $ansEvent->getOutputObject();
        $this->assertSame("proceed ans", $reply->getSwapTagString("message"), "reply message not as expected");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "Status code is not as expected");

        $this->assertSame(2,$messages->countInDB()->items,"Incorrect number of messages in outbox");

        $avatar = new Avatar();
        $avatar->loadId(2);
        $this->assertSame(100, $avatar->getCredits(), "Expected credit level is wrong");

        $this->assertSame(1,$transactions->countInDB()->items,"Incorrect number of transactions");

    }
}
