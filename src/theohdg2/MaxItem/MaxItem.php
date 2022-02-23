<?php

namespace theohdg2\Maxitem;

use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class MaxItem extends PluginBase{

    protected function onEnable(): void
    {
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task{

            private MaxItem $main;

            public function __construct(MaxItem $thiss)
            {
                $this->main = $thiss;
            }

            public function onRun(): void
            {
                foreach (Server::getInstance()->getOnlinePlayers() as $player){
                    foreach ($this->main->getConfig()->get("maxItem",[]) as $itemData){
                        $count = 0;
                        $index= [];
                        foreach ($player->getInventory()->getContents(true) as $slot => $item){
                            if($item->getId() === $itemData["id"] && $item->getMeta() == $itemData["meta"]){
                                $count+= $item->getCount();
                                $index[] = $slot;
                            }
                        }
                        if($count > $itemData["max"]){
                            foreach ($index as $i){
                                $player->getInventory()->setItem($i,ItemFactory::air());
                            }
                            $player->getInventory()->addItem(ItemFactory::getInstance()->get($itemData["id"],$itemData["meta"],$itemData["max"]));
                        }
                        if($this->main->getConfig()->get("drop-item")){
                            $r = $count - $itemData["max"];
                            $player->getWorld()->dropItem($player->getPosition(),ItemFactory::getInstance()->get($itemData["id"],$itemData["meta"],$r));
                        }

                    }
                }
            }
        },10);
    }

}