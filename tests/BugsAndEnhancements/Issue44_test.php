<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Package\Remove;
use App\R7\Model\Package;
use App\R7\Model\Transactions;
use App\R7\Set\AvatarSet;
use PHPUnit\Framework\TestCase;

class Issue44 extends TestCase
{
    protected $package = null;
    public function test_removePackageUnlinkTransaction()
    {        
        global $sql, $page, $_POST;
        $avatars = new AvatarSet();
        $avatars->loadNewest(1);
        $avatar = $avatars->getFirst();
        $package = new Package();
        $package->loadByName("is43");
        $transaction = new Transactions();
        $transaction->setTransactionUid("is44");
        $transaction->setAvatarLink($avatar->getId());
        $transaction->setPackageLink($package->getId());
        $transaction->setUnixtime(time());
        $transaction->setAmount(44);
        $createsStatus = $transaction->createEntry();
        $this->assertSame("ok",$createsStatus["message"],"Failed to create transaction");
        $this->assertSame(true,$createsStatus["status"],"Failed to create transaction");
        $sql->sqlSave();

        $page = $package->getPackageUid();
        $removeProcess = new Remove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Package removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
        $sql->sqlSave();

        $transaction_test = new Transactions();
        $status = $transaction_test->loadByTransactionUid("is44");
        $this->assertSame(true,$status,"Failed to load transaction to test");
        $this->assertSame(null,$transaction_test->getPackageLink(),"Failed to unlink package");
    }
}
