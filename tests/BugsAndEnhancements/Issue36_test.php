<?php

namespace StreamAdminR7;

use App\Endpoint\SecondLifeApi\Renew\Details;
use App\Helpers\AvatarHelper;
use App\Models\Avatar;
use PHPUnit\Framework\TestCase;

class Issue36 extends TestCase
{
    protected $package = null;
    public function test_ChangeUsernameInSl()
    {        
        $AvatarHelper = new AvatarHelper();
        $reply = $AvatarHelper->loadOrCreate("499c3e36-69b3-40e5-9229-0cfa5db30766"); 
        // no name given so the name should not have changed
        $this->assertSame(true,$reply,"Failed to load avatar");
        $this->assertSame("Test Buyer",$AvatarHelper->getAvatar()->getAvatarName());
        $AvatarHelper = new AvatarHelper();
        $reply = $AvatarHelper->loadOrCreate("499c3e36-69b3-40e5-9229-0cfa5db30766","James pond"); 
        $this->assertSame(true,$reply,"Failed to load avatar (And change its name)");
        $this->assertSame("James pond",$AvatarHelper->getAvatar()->getAvatarName()); 
        // the name should have been updated.
    }
}
