<?php

namespace StreamAdminR7;

use App\Endpoint\Control\Transactions\Remove as TransactionsRemove;
use App\Endpoint\View\Transactions\DefaultView;
use App\Endpoint\View\Transactions\Inrange;
use App\Helpers\AvatarHelper;
use App\Helpers\RegionHelper;
use App\Helpers\ResellerHelper;
use App\Helpers\TransactionsHelper;
use App\Models\Package;
use App\Models\Server;
use App\Models\Stream;
use App\Models\Transactions;
use Tests\Mytest;

class TransactionsTest extends Mytest
{
    public function test_Default()
    {
        $avatarhelper = new AvatarHelper();
        $status = $avatarhelper->loadOrCreate("2f9c3e36-6fb3-40c5-92f9-0c6a5d230f66","TransactionTest Avatar");
        $this->assertSame(true,$status,"Unable to find a avatar to use");
        $avatar = $avatarhelper->getAvatar();
        $resellerhelper = new ResellerHelper();
        $status = $resellerhelper->loadOrCreate(1,true,40);
        $this->assertSame(true,$status,"Unable to find a reseller to use");
        $reseller = $resellerhelper->getReseller();
        $stream = new Stream();
        $status = $stream->loadID(1);
        $this->assertSame(true,$status->status,"Unable to find a stream to use");
        $server = new Server();
        $status = $server->loadID($stream->getServerLink());
        $this->assertSame(true,$status->status,"Unable to find a server to use");
        $package = new Package();
        $status = $package->loadID(1);
        $this->assertSame(true,$status->status,"Unable to find a package to use");
        $regionHelper = new RegionHelper();
        $status = $regionHelper->loadOrCreate("Unittest");
        $this->assertSame(true,$status,"Unable to find a region to use");
        $region = $regionHelper->getRegion();
        $TransactionsHelper = new TransactionsHelper();
        $loop = 10;
        while($loop > 0) {
            $amount = 1000 - (5*$loop);
            $renewal = (time() + $loop) % 3;
            $flag = false;
            if($renewal < 1) { 
                $flag = true;
            }
            $status = $TransactionsHelper->createTransaction($avatar,$package,$stream,$reseller,$region,$amount,$flag);
            if($status != true) {
                $this->assertSame(true,$status,"Error creating a test transaction");
                break;
            }
            $loop--;
        }
        $status = $TransactionsHelper->createTransaction($avatar,$package,$stream,$reseller,$region,4000,false,mktime(12,12,12,12,12,2019));
        $this->assertSame(true,$status,"Error creating a test transaction");
        $default = new DefaultView();
        $default->process();
        $statuscheck = $default->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing transactions list element";
        $this->assertStringContainsString("Client",$statuscheck,$missing);
        $this->assertStringContainsString("Region",$statuscheck,$missing);
        $this->assertStringContainsString("Type",$statuscheck,$missing);
        $this->assertStringContainsString("Transactions/Remove/",$statuscheck,$missing);
        $this->assertStringContainsString("Select transation period",$statuscheck,$missing);
        $this->assertStringContainsString("2021",$statuscheck,$missing);
    }

    /**
     * @depends test_Default
     */
    public function test_RemoveProcess()
    {
        global $system, $_POST;
        $transaction = new Transactions();
        $status = $transaction->loadByField("amount",995);
        $this->assertSame(true,$status->status,"Unable to find a transaction to use");
        $system->setPage($transaction->getTransactionUid());

        $removeProcess = new TransactionsRemove();
        $_POST["accept"] = "Accept";
        $removeProcess->process();
        $statuscheck = $removeProcess->getOutputObject();
        $this->assertStringContainsString("Transaction removed",$statuscheck->getSwapTagString("message"));
        $this->assertSame(true,$statuscheck->getSwapTagBool("status"),"Status check failed");
    }

    /**
     * @depends test_Default
     */
    public function test_TransactionPeriod()
    {
        global $_GET;
        $_GET["month"] = "12";
        $_GET["year"] = "2019";
        $view = new Inrange();
        $view->process();
        $statuscheck = $view->getOutputObject()->getSwapTagString("page_content");
        $missing = "Missing transactions inrange element";
        $this->assertStringContainsString("4000",$statuscheck,$missing);
        $this->assertStringContainsString('SELECTED>Dec',$statuscheck,$missing);
        $this->assertStringContainsString('SELECTED>2019',$statuscheck,$missing);
    }


}
