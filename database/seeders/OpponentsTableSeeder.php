<?php

namespace Database\Seeders;

use App\Models\Opponent;
use Illuminate\Database\Seeder;

class OpponentsTableSeeder extends Seeder
{
    public function run(): void
    {
        $opponents = [
            ['name' => 'DCV', "location" => 'Krimpen aan den IJssel', 'logo' => 'https://website.storage/Data/DCV/RTE/Afbeeldingen/MenuItem/439/badge_kleur.png?maxwidth=1200&maxheight=630', 'latitude' => 51.90894487593641, 'longitude' => 4.589017207382628],
            ['name' => 'Lekkerkerk', "location" => 'Lekkerkerk', 'logo' => 'https://website.storage/Data/Lekkerkerk/Layout/Files/SocialMediaStandaardMeta.jpg?637145275955548998&maxwidth=1200&maxheight=630', 'latitude' => 51.9020305082625, 'longitude' => 4.677412069296964],
            ['name' => 'Spirit', "location" => 'Ouderkerk aan den IJssel', 'logo' => 'https://www.vvspirit.nl/wp-content/uploads/vvspirit/2017/07/logo-512-512.png', 'latitude' => 51.930529846039356, 'longitude' => 4.643731584638417],
            ['name' => 'Capelle', "location" => 'Capelle aan den IJssel', 'logo' => 'https://website.storage/Data/Capelle/Layout/Files/SocialMediaStandaardMeta.jpg?638876133700421820&maxwidth=1200&maxheight=630xqFwoTCIiv5LXGgJADFQAAAAAdAAAAABAE', 'latitude' => 51.921958376361715, 'longitude' => 4.577286240462604],
            ['name' => 'Nieuwerkerk', "location" => 'Nieuwerkerk aan den IJssel', 'logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ8vfcDGmDBgIg42Brf1luP7P8DrsQZAkwuPA&s', 'latitude' => 51.96152078537211, 'longitude' => 4.601430144325356],
            ['name' => 'Dilettant', "location" => 'Krimpen aan de Lek', 'logo' => 'https://website.storage/Data/Dilettant/Layout/Files/SocialMediaStandaardMeta.jpg?638946471345387548&maxwidth=1200&maxheight=630', 'latitude' => 51.898803015011104, 'longitude' => 4.637758716235674],
            ['name' => 'Gouderak', "location" => 'Gouderak', 'logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT_YfDG7ysNBXVAhPywFMOuyvT_Kv4Lv6tWsQ&s', 'latitude' => 51.97477114410598, 'longitude' => 4.653936768364386],
            ['name' => "Alexandria'66", "location" => 'Rotterdam', 'logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ1i4gNeGUQw2uvnA1br1fvtgjfLhkIHKMUFQ&s', 'latitude' => 51.9380273746122, 'longitude' => 4.560114306993028],
            ['name' => 'TOGB', "location" => 'Berkel en Rodenrijs', 'logo' => 'https://voetbal.togb.nl/wp-content/uploads/voetbaltogb/2019/04/cropped-logo-512-512.png', 'latitude' => 52.001173156347676, 'longitude' => 4.472089281628528],
            ['name' => 'Perkouw', "location" => 'Berkenwoude', 'logo' => 'https://website.storage/Data/Perkouw/Layout/Files/SocialMediaStandaardMeta.jpg?638619491448137738&maxwidth=1200&maxheight=630', 'latitude' => 51.948791629801235, 'longitude' => 4.705811699047375],
            ['name' => 'DSO', "location" => 'Zoetermeer', 'logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRAysXB_0X1d3ZT4tPtHNHLW9TDbkC90rvqmg&s', 'latitude' => 52.06475699840031, 'longitude' => 4.555989468161038],
            ['name' => 'CKC', "location" => 'Rotterdam', 'logo' => 'https://www.vvckc.nl/wp-content/uploads/vvckc/2017/06/logo-512-512.png', 'latitude' => 51.911400658450155, 'longitude' => 4.555925370209573],
            ['name' => 'BVCB', "location" => 'Bergschenhoek', 'logo' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQk3Ce8HcMXWKUvqququcCzCu1MIk9fQgoNUA&s', 'latitude' => 51.98236636258762, 'longitude' => 4.512371653022545],
        ];

        foreach ($opponents as $opponent) {
            Opponent::create($opponent);
        }
    }
}
