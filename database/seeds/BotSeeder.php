<?php

use App\Domain\Bot;
use Doctrine\ORM\EntityManager;
use Illuminate\Database\Seeder;

class BotSeeder extends Seeder
{
    private array $names = [
        'Baller1993' => 'Baller1993',
        'Phinsmania' => 'Phinsmania',
        'Billsmafia3' => 'Billsmafia3',
        'Cromartiekids14' => 'Cromartiekids14',
        'Bradygoat' => 'Bradygoat',
        'Mahomessss' => 'Mahomessss',
        'MahomesSB' => 'MahomesSB',
        'Belichikscutsleeves' => 'Belichikscutsleeves',
        'NYC4lyfe' => 'NYC4lyfe',
        'Trubiskytrash' => 'Trubiskytrash',
        'LAsucks' => 'LAsucks',
        'Xtremespeed' => 'Xtremespeed',
        'Turnychamp1' => 'Turnychamp1',
        'alltimeGOAT' => 'alltimeGOAT',
        'puck45' => 'puck45',
        'Metawp' => 'Metawp',
        'StoneCold' => 'StoneCold',
        'Tequilamagic' => 'Tequilamagic',
        'Getthatmoney' => 'Getthatmoney',
        'Whizkids' => 'Whizkids',
        'Lebron1MJ2' => 'Lebron1MJ2',
        'GoldenKnightsss' => 'GoldenKnightsss',
        'Coutnryroads' => 'Coutnryroads',
        'TrueNorth' => 'TrueNorth',
        'Gaitorsblow' => 'Gaitorsblow',
        'Nole4ever' => 'Nole4ever',
        'Jim6767' => 'Jim6767',
        'SweetHome' => 'SweetHome',
        'SweetJJJ' => 'SweetJJJ',
        'Ridethebus' => 'Ridethebus',
        'Bears85' => 'Bears85',
        'SuperBowlLA' => 'SuperBowlLA',
        'wildthing' => 'wildthing',
        'Thinlyslicedlox' => 'Thinlyslicedlox',
        'Tupacisalive' => 'Tupacisalive',
        'MAGA2020' => 'MAGA2020',
        'duckTrump' => 'duckTrump',
        'Oilersinedm' => 'Oilersinedm',
        'LALAland' => 'LALAland',
        'Legend1' => 'Legend1',
        'AshKechum' => 'AshKechum',
        'Dodgeawrench' => 'Dodgeawrench',
        'Steelers6rings' => 'Steelers6rings',
        'RainbowDwayneBowe' => 'RainbowDwayneBowe',
        'HartfordWahlers80' => 'HartfordWahlers80',
        'Hasselbackhair' => 'Hasselbackhair',
        'Blackjackstud' => 'Blackjackstud',
        'gooseisloose' => 'gooseisloose',
        'Judgeallrise' => 'Judgeallrise',
        'SwolelikeStanton' => 'SwolelikeStanton',
        'OverTyreekHil' => 'OverTyreekHil',
        'JoshAllenBombs' => 'JoshAllenBombs',
        '747outathesky' => '747outathesky',
        'CoolJAmesDean' => 'CoolJAmesDean',
        'Sfhomeless' => 'Sfhomeless',
        'Lovethosetatas' => 'Lovethosetatas',
        'Sowtheseeds' => 'Sowtheseeds',
        'SayHeyMays' => 'SayHeyMays',
        'onlycoyotesfan' => 'onlycoyotesfan',
        'PatriotsCheat' => 'PatriotsCheat',
        'Lionsrlosers' => 'Lionsrlosers',
        'swisscheeseD' => 'swisscheeseD',
        'Donaldthefreak' => 'Donaldthefreak',
        'thirstythursday' => 'thirstythursday',
        'always420' => 'always420',
        'calikush4free' => 'calikush4free',
        'ballinlikeHarden' => 'ballinlikeHarden',
        'PabloLopezswag' => 'PabloLopezswag',
        'InjuredJedLowrie' => 'InjuredJedLowrie',
        'PoorPdiddy' => 'PoorPdiddy',
        'Howboutthemcowboys' => 'Howboutthemcowboys',
        'sweetgeorgiapeach' => 'sweetgeorgiapeach',
        'firstnotlast' => 'firstnotlast',
        'RonBurgundy' => 'RonBurgundy',
        'bansingleplyTP' => 'bansingleplyTP',
        'NigerianNightmare' => 'NigerianNightmare',
        'Thefreakkearse' => 'Thefreakkearse',
        'DerrickHenryinHS' => 'DerrickHenryinHS',
        'bichesrcrazy' => 'bichesrcrazy',
        'Locknessmonster' => 'Locknessmonster',
        'releasethekraken' => 'releasethekraken',
        'sleeplessinSeattle' => 'sleeplessinSeattle',
        '2002Angels' => '2002Angels',
        'MoneyballAs' => 'MoneyballAs',
        'SweetswingYelich' => 'SweetswingYelich',
        'NapolianDynomite' => 'NapolianDynomite',
        'Kanye2024' => 'Kanye2024',
        'smokethatloud' => 'smokethatloud',
        'backtheblue' => 'backtheblue',
        'SuperBowlEli' => 'SuperBowlEli',
        'TheMatthewsFamily' => 'TheMatthewsFamily',
        'HighRoller777' => 'HighRoller777',
        'ParlayPeter' => 'ParlayPeter',
        'Lambo4me' => 'Lambo4me',
        'Thetoplegend' => 'Thetoplegend',
        'fratbroBama' => 'fratbroBama',
        'WarEagleAuburn' => 'WarEagleAuburn',
        'Rollllllllllltide' => 'Rollllllllllltide',
        'Myusernamebasic' => 'Myusernamebasic',
        'HolyGrail' => 'HolyGrail',
        'BonnieandClyde' => 'BonnieandClyde',
        'Thebashbrothers' => 'Thebashbrothers',
        'candlestickbreeze' => 'candlestickbreeze',
        'MagicJohnsonTinder' => 'MagicJohnsonTinder',
        '5oclocksomewhere' => '5oclocksomewhere',
        'HotelCalifornia77' => 'HotelCalifornia77',
        'Betterdeadthanred' => 'Betterdeadthanred',
        'Buckeyes10str8' => 'Buckeyes10str8',
        'Richlikerockefeller' => 'Richlikerockefeller',
        'TomBrady12' => 'TomBrady12',
        'CamdenYardsBLT' => 'CamdenYardsBLT',
        'NoMarlinsFans' => 'NoMarlinsFans',
        'Fearthestache' => 'Fearthestache',
        'CleanJoshGordon' => 'CleanJoshGordon',
        'Inshapepablosandoval' => 'Inshapepablosandoval',
        'BLMrichardsherman' => 'BLMrichardsherman',
        'sleepycreepyjoebiden' => 'sleepycreepyjoebiden',
        'MAGATrumpPence' => 'MAGATrumpPence',
        'Rememberthetitans' => 'Rememberthetitans',
        'Medicare4All' => 'Medicare4All',
        'BuffaloBlueJays' => 'BuffaloBlueJays',
        'RollieFingersStache' => 'RollieFingersStache',
        'Runthefootball' => 'Runthefootball',
        'bustlikejamarcusrussell' => 'bustlikejamarcusrussell',
        'paigow21' => 'paigow21',
        'Ogbettingchamp' => 'Ogbettingchamp',
        'GamblerPeteRose' => 'GamblerPeteRose',
        'Linsanity' => 'Linsanity',
        'TheGreekFreak' => 'TheGreekFreak',
        '24CupsCanadiens' => '24CupsCanadiens',
        'SodanotPop' => 'SodanotPop',
        'Covid19Scamdemic' => 'Covid19Scamdemic',
        'RideitlikeaFord' => 'RideitlikeaFord',
        'GeauxTigers' => 'GeauxTigers',
        '4HorsemenND' => '4HorsemenND',
        'SpecialEndingMassage' => 'SpecialEndingMassage',
        'JUJUJUJUJUPit' => 'JUJUJUJUJUPit',
        'BittersweetSymphony' => 'BittersweetSymphony',
        'BlackMcCaffrey' => 'BlackMcCaffrey',
        'TheWhiteLarryBird' => 'TheWhiteLarryBird',
        'WiltTheStilt100' => 'WiltTheStilt100',
        'SugaShow' => 'SugaShow',
        'CheatinBlackSox' => 'CheatinBlackSox',
        'MantiTeoGF' => 'MantiTeoGF',
        'JerryRiceGOAT' => 'JerryRiceGOAT',
        'SweatnessWP' => 'SweatnessWP',
        'FlyhighEagles' => 'FlyhighEagles',
        'BurrowOhio' => 'BurrowOhio',
        'MusicCityMiracle' => 'MusicCityMiracle',
        'Trocheckwastripped' => 'Trocheckwastripped',
        'Puigmania' => 'Puigmania',
        'PapaJohnsPeyton' => 'PapaJohnsPeyton',
        'TakeoSpikesNeck' => 'TakeoSpikesNeck',
        'ScabBack' => 'ScabBack',
        'Royalflush' => 'Royalflush',
        '2Fly4AWiFi' => '2Fly4AWiFi',
        '3RayAllen' => '3RayAllen',
        'cryinlikeCutler' => 'cryinlikeCutler',
        'Undefeated72PHins' => 'Undefeated72PHins',
        'TampaBayYucks' => 'TampaBayYucks',
        'GoGoldenKnights' => 'GoGoldenKnights',
        'TheSilentKiller' => 'TheSilentKiller',
        'Gameliketiger' => 'Gameliketiger',
        'JohnDalyPants' => 'JohnDalyPants',
        'Dabs4dayzzz' => 'Dabs4dayzzz',
        'Sharpsandsquares' => 'Sharpsandsquares',
        'PeytonandElimiddlebro' => 'PeytonandElimiddlebro',
        'AthletelikeJimThorpe' => 'AthletelikeJimThorpe',
        'babygotdak44' => 'babygotdak44',
        'Heatfan33' => 'Heatfan33',
        'CountryRoadFL' => 'CountryRoadFL',
        'Marinoballs1958' => 'Marinoballs1958',
        'Infinityplay9' => 'Infinityplay9',
        'Tua2020' => 'Tua2020',
        'Fitzmagic99' => 'Fitzmagic99',
        'Champdaddy' => 'Champdaddy',
        'Tebowdaddy' => 'Tebowdaddy',
        'Wadeballer5' => 'Wadeballer5',
        'runtheball90' => 'runtheball90',
        'clownportnoy' => 'clownportnoy',
        'dankystanky' => 'dankystanky',
        'deionscat8' => 'deionscat8',
        'pumpngun' => 'pumpngun',
        'darnold4pres' => 'darnold4pres',
        'kingjamesPA' => 'kingjamesPA',
        'largencharge1995' => 'largencharge1995',
        'MURRAYUP' => 'MURRAYUP',
        'Mahomesyodaddy' => 'Mahomesyodaddy',
        'ReturningChumps' => 'ReturningChumps',
        'letsgetfiscal33' => 'letsgetfiscal33',
        'humantorchdown9' => 'humantorchdown9',
        'meanjoebean' => 'meanjoebean',
        'megatrondet11' => 'megatrondet11',
        'leeflicker' => 'leeflicker',
        'firstandjenga' => 'firstandjenga',
        'badlewser44' => 'badlewser44',
        'bradysdagoat44' => 'bradysdagoat44',
        'nyjettrain' => 'nyjettrain',
        'mrrodgers88' => 'mrrodgers88',
        'kobe4life' => 'kobe4life',
        'krispycremess' => 'krispycremess',
        'sundayscaries69' => 'sundayscaries69',
        'Cowboyup44' => 'Cowboyup44',
        'tjfant9' => 'tjfant9',
        'sabansucks' => 'sabansucks',
        'freeplaychampz' => 'freeplaychampz',
        'washingtonwatkin' => 'washingtonwatkin',
        'mercyrules4' => 'mercyrules4',
        'VonMiller9' => 'VonMiller9',
        'Thielenshog' => 'Thielenshog',
        'Bakerbozo00' => 'Bakerbozo00',
        'Djcummings' => 'Djcummings',
        'Arodandjlo9' => 'Arodandjlo9',
        'Creekbakblok' => 'Creekbakblok',
        'OJsInnocent6' => 'OJsInnocent6',
        'salamislingger' => 'salamislingger',
        'fightinpinata' => 'fightinpinata',
        'RustyTromb66666' => 'RustyTromb66666',
        'pokerfunnnnz' => 'pokerfunnnnz',
        'FillyFakers' => 'FillyFakers',
        'Russellmuscles2' => 'Russellmuscles2',
        'AaronandSuh' => 'AaronandSuh',
        'PeytonownsEli' => 'PeytonownsEli',
        'Pizzafingers' => 'Pizzafingers',
        'Trumpsdingo' => 'Trumpsdingo',
        'Roethlisburgers1' => 'Roethlisburgers1',
        'Joethecannon' => 'Joethecannon',
        'bringbritneybak' => 'bringbritneybak',
        'Skullcooper' => 'Skullcooper',
        'Crymearivers7' => 'Crymearivers7',
        'Glashalfphil' => 'Glashalfphil',
        'dacommishh' => 'dacommishh',
        'Charlottesbest' => 'Charlottesbest',
        'Jessicab5' => 'Jessicab5',
        'Gregb99' => 'Gregb99',
        'Jacksonsclaw' => 'Jacksonsclaw',
        'beastmode120' => 'beastmode120',
        'VictoriaSFLA' => 'VictoriaSFLA',
        '12angrymen' => '12angrymen',
        'Aaronitout' => 'Aaronitout',
        'Armedrodgery' => 'Armedrodgery',
        'Gentletouch81' => 'Gentletouch81',
        'Ojmcduff8' => 'Ojmcduff8',
        'Hillaryscruff' => 'Hillaryscruff',
        'prymetymer0' => 'prymetymer0',
        'Vinegarstrok1111' => 'Vinegarstrok1111',
        'Houstontexfan' => 'Houstontexfan',
        'Canadaworms4' => 'Canadaworms4',
        'Texmex9' => 'Texmex9',
        'Bigggfan' => 'Bigggfan',
        'lilyisqueen' => 'lilyisqueen',
        'wconwayyy' => 'wconwayyy',
        'AndrewTheGiant' => 'AndrewTheGiant',
        'Crystalpalace33' => 'Crystalpalace33',
        'WestHammster' => 'WestHammster',
        'Eatingreens' => 'Eatingreens',
        'Veggiesquaddd' => 'Veggiesquaddd',
        'Naturesninjas' => 'Naturesninjas',
        'Zhang237' => 'Zhang237',
        'Amari2211' => 'Amari2211',
        'DarthRaider99' => 'DarthRaider99',
        'deflatriots7' => 'deflatriots7',
        'Carramari' => 'Carramari',
        'justgurleytank' => 'justgurleytank',
        'burnunitz' => 'burnunitz',
        'savedbyodel' => 'savedbyodel',
        'Packatacksnack' => 'Packatacksnack',
        'GBDACHAMP' => 'GBDACHAMP',
        'kawhiyomoney' => 'kawhiyomoney',
        'russellnohustle' => 'russellnohustle',
        'Anthony3468' => 'Anthony3468',
        'Mrclutchpicks' => 'Mrclutchpicks',
        'StevemLL' => 'StevemLL',
        'CPaul900' => 'CPaul900',
        'Kareemthedreem' => 'Kareemthedreem',
        'Mikeyfish1068' => 'Mikeyfish1068',
        'Trumptheking' => 'Trumptheking',
        'LHPGarlicandsoy' => 'LHPGarlicandsoy',
        'Homersandbeers' => 'Homersandbeers',
        'RudyMagic' => 'RudyMagic',
        'Lukafan9' => 'Lukafan9',
        'Kembadream' => 'Kembadream',
        'Biggerbol' => 'Biggerbol',
        'MountZion' => 'MountZion',
        'StormSerge22' => 'StormSerge22',
        'BillyD2' => 'BillyD2',
        'LonzoGators' => 'LonzoGators',
        'Algreen4' => 'Algreen4',
        'LosToros' => 'LosToros',
        'JohnCandy02' => 'JohnCandy02',
        'VegasTerrors' => 'VegasTerrors',
        'Blackjackkid' => 'Blackjackkid',
        'Skyhook' => 'Skyhook',
        'JagrBombs1' => 'JagrBombs1',
        'KCEEfloyd' => 'KCEEfloyd',
        'JoelEmbieeed' => 'JoelEmbieeed',
        'Stanthemann' => 'Stanthemann',
        'Overfgs' => 'Overfgs',
        'JimmyGHOG' => 'JimmyGHOG',
        'Mavsdachamps' => 'Mavsdachamps',
        'Giannisquad' => 'Giannisquad',
        'Redskins2020' => 'Redskins2020',
        'Miamisuns9' => 'Miamisuns9',
        'Shaqdaddy00' => 'Shaqdaddy00',
        'Jimmybats99' => 'Jimmybats99',
        'Maggiepie' => 'Maggiepie',
        'Theman6310' => 'Theman6310',
        'Bronxbabies5' => 'Bronxbabies5',
        'Danspringer1' => 'Danspringer1',
        'Isabellaella' => 'Isabellaella',
        'Mrgovnor' => 'Mrgovnor',
        'dukedaddy' => 'dukedaddy',
        'Cornelldream' => 'Cornelldream',
        'Tulsafighter2' => 'Tulsafighter2',
        'cusesquaddd' => 'cusesquaddd',
        'michaelporter9' => 'michaelporter9',
        'rakeyjakey' => 'rakeyjakey',
        'SHIPTHECHIP2' => 'SHIPTHECHIP2',
    ];

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run()
    {
        $bots = $this->entityManager->getRepository(Bot::class)->findBy(['name' => current($this->names)]);

        if (count($bots)) {
            return;
        }

        foreach ($this->names as $name) {
            $this->entityManager->persist(Bot::create($name));
        }

        $this->entityManager->flush();
    }
}
