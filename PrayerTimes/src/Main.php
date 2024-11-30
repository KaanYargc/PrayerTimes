<?php

declare(strict_types=1);

namespace yargc\PrayerTimes;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use yargc\PrayerTimes\Vecnavium\FormsUI\CustomForm;

class Main extends PluginBase
{

    protected function onEnable(): void
    {
        $this->getLogger()->info("PrayerTimes has been enabled");
    }

    protected function onDisable(): void
    {
        $this->getLogger()->info("PrayerTimes has been disabled");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() !== "namaz") {
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage("§cUsage: /prayertimes <city>");
            return false;
        }

        $city = $args[0];
        $this->PrayerTimes($sender, $city);
        return true;
    }

    public function form(Player $player, string $response): void
    {
        $form = new CustomForm(function (Player $player, array $data) use ($response): bool {

            return true;
        });
        $form->setTitle("Namaz Vakitleri");
        $form->addLabel($response);
        $player->sendForm($form);
    }

    public function PrayerTimes(Player $player, string $city): bool
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.collectapi.com/pray/all?data.city=$city",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                "authorization: apikey 1PppfBhrTC8Zt7nCqdiZbx:4j5CZb5ffzR8RBWRGjMtkN",
                "content-type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $player->sendMessage("cURL Error #:" . $err);
            return false;
        } else {
            $data = json_decode($response, true);
            if (isset($data['result'])) {
                $times = $data['result'];
                $message = $city."§l§a için namaz saatleri şunlardır:\n§r";
                foreach ($times as $time) {
                    if (isset($time['vakit']) && isset($time['saat'])) {
                        $message .= "- " . $time['vakit'] . ": " . $time['saat'] . "\n";
                    } else {
                        $player->sendMessage("Invalid data format from API");
                        return false;
                    }
                }
                //$player->sendMessage($message);
                $this->form($player, $message);

                return true;
            } else {
                $player->sendMessage("Invalid response from API");
                return false;
            }
        }
    }
}