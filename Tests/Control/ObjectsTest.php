<?php

namespace Tests\Control;

use App\Endpoint\Control\Notice\Create;
use App\Endpoint\Control\Notice\Remove;
use App\Endpoint\Control\Notice\Update;
use App\Endpoint\Control\Objects\Clear;
use App\Models\Notice;
use App\Models\Objects;
use App\Models\Region;
use App\Models\Sets\ObjectsSet;
use Tests\TestWorker;

class ObjectsTest extends TestWorker
{
    public function test_Clear()
    {
        $region = new Region();
        $region->setName("Unittest");
        $reply = $region->createEntry();
        $this->assertSame(true, $reply->status, "failed to create region");
        // fill in some test objects
        $loop = 0;
        while ($loop < 50) {
            $object = new Objects();
            $uid = $object->createUID("objectUUID", 8);
            $this->assertSame(true, $uid->status, "failed to get uid");
            $object->setRegionLink($reply->newId);
            $object->setAvatarLink(1);
            $object->setObjectUUID("unittest" . $uid->uid);
            $object->setObjectName("unittest" . $loop);
            $object->setObjectMode("test");
            $object->setObjectXYZ("123,456,555");
            $create = $object->createEntry();
            $this->assertSame(true, $create->status, "failed to create object");
            if ($create->status == false) {
                break;
            }
            $loop++;
        }
        $objectsSet = new ObjectsSet();
        $this->assertSame(50, $objectsSet->countInDB()->items, "incorrect number of entrys in db");
        $_POST["accept"] = "Accept";
        $clear = new Clear();
        $clear->process();
        $reply = $clear->getOutputObject();
        $this->assertSame("Objects cleared from DB", $reply->getSwapTagString("message"), "Message does not appear to be correct");
        $this->assertSame(true, $reply->getSwapTagBool("status"), "incorrect status code");
        $this->assertSame(0, $objectsSet->countInDB()->items, "incorrect number of entrys in db");
    }
}
