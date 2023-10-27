<?php

use App\Models\Ad;
use Illuminate\Support\Facades\Route;
use Goutte\Client;
use NotificationChannels\Telegram\TelegramUpdates;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
        // parse url
        $url = 'https://999.md/ro/list/real-estate/apartments-and-rooms?hide_duplicates=yes&applied=1&show_all_checked_childrens=no&eo=12900,12912,12885,13859&o_32_9_12900_13859=15669,15667&o_33_1=776&ef=2307,30,2203&o_2203_795=18895';

        // create new client

        $client = new Client();
        $crawler = $client->request('GET', 'https://999.md/ro/list/real-estate/apartments-and-rooms?hide_duplicates=yes&applied=1&show_all_checked_childrens=no&eo=12900,12912,12885,13859&o_32_9_12900_13859=15669,15667&o_33_1=776&ef=2307,30,2203&o_2203_795=18895');

        // Verifică dacă lista de noduri nu este goală
        if ($crawler->count()) {
            $crawler->filter('.ads-list-photo-item')->each(function ($node) {
                if ($node->filter('.ads-list-photo-item-title a')->count()) {
                    $title = $node->filter('.ads-list-photo-item-title a')->text();
                    $link = $node->filter('.ads-list-photo-item-title a')->attr('href');
                    $priceWrapper = $node->filter('.ads-list-photo-item-price-wrapper')->text();
                    // check if price is per meter exists
                    if ($node->filter('.is-price-per-meter')->count() > 0) {
                        $isPricePerMeter = $node->filter('.is-price-per-meter')->text();
                    } else {
                        $isPricePerMeter = '---';
                    }
                    if (strpos($link, 'booster') === false) {
                        $existingAd = Ad::where('link', $link)->first();

                        if (!$existingAd) {
                            $ad = new Ad([
                                'title' => $title,
                                'link' => $link,
                                'price' => $priceWrapper,
                                'per_m2' => $isPricePerMeter
                            ]);
                            $ad->save();

                            \Illuminate\Support\Facades\Notification::route('telegram', env('TELEGRAM_CHANNEL_ID'))
                                ->notify(new \App\Notifications\NewAdParsed($ad));
                        }
                    }
                }
            });
        } else {
            // Tratează cazul în care lista de noduri este goală
            \Illuminate\Support\Facades\Log::error('Nu s-au găsit elemente valide pe pagina web.');
        }

});
