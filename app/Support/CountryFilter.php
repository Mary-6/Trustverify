<?php

namespace App\Support;

use Illuminate\Support\Str;

class CountryFilter
{
    public static function isCountrySpecific(string $value): bool
    {
        $name = ' '.Str::lower($value).' ';
        $terms = self::terms();

        if (preg_match_all('/\[([^\]]+)\]/', $name, $matches)) {
            foreach ($matches[1] as $bracket) {
                $normalized = trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/[^a-z0-9]+/', ' ', $bracket)));
                if ($normalized === '') {
                    continue;
                }
                if (isset($terms[$normalized])) {
                    return true;
                }
                foreach (preg_split('/\s+/', $normalized) ?: [] as $part) {
                    if (isset($terms[$part])) {
                        return true;
                    }
                }
            }
        }

        foreach ($terms as $term => $unused) {
            if (strlen($term) < 3) {
                continue;
            }
            if (preg_match('/(?<![a-z0-9])'.preg_quote($term, '/').'(?![a-z0-9])/u', $name) === 1) {
                return true;
            }
        }

        return false;
    }

    private static function terms(): array
    {
        static $terms = null;

        if ($terms !== null) {
            return $terms;
        }

        return $terms = [
            'afghanistan' => true, 'albania' => true, 'albanian' => true,
            'algeria' => true, 'algerian' => true, 'andorra' => true,
            'angola' => true, 'argentina' => true, 'argentinian' => true, 'argentine' => true,
            'armenia' => true, 'australia' => true, 'australian' => true, 'austria' => true, 'austrian' => true,
            'azerbaijan' => true, 'bahamas' => true, 'bahamian' => true, 'bahrain' => true, 'bangladesh' => true, 'bangladeshi' => true,
            'barbados' => true, 'barbadian' => true, 'belarus' => true, 'belarusian' => true, 'belgium' => true, 'belgian' => true,
            'belize' => true, 'belizean' => true, 'benin' => true, 'beninese' => true, 'bhutan' => true,
            'bolivia' => true, 'bolivian' => true, 'bosnia' => true, 'bosnia and herzegovina' => true, 'bosnian' => true,
            'botswana' => true, 'botswanan' => true, 'brazil' => true, 'brazilian' => true, 'brunei' => true,
            'bulgaria' => true, 'bulgarian' => true, 'burkina faso' => true, 'burundi' => true, 'burundian' => true,
            'cabo verde' => true, 'cambodia' => true, 'cameroon' => true, 'cameroonian' => true, 'canada' => true, 'canadian' => true,
            'central african republic' => true, 'chad' => true, 'chadian' => true, 'chile' => true, 'chilean' => true,
            'china' => true, 'chinese' => true, 'colombia' => true, 'colombian' => true, 'comoros' => true,
            'congo' => true, 'congolese' => true, 'costa rica' => true, 'costa rican' => true, 'cote divoire' => true, 'ivory coast' => true,
            'croatia' => true, 'croatian' => true, 'cuba' => true, 'cuban' => true, 'curacao' => true, 'cyprus' => true,
            'czech republic' => true, 'czechia' => true, 'czech' => true, 'denmark' => true, 'danish' => true,
            'djibouti' => true, 'dominica' => true, 'dominican republic' => true, 'dominican' => true,
            'dr congo' => true, 'democratic republic of the congo' => true, 'ecuador' => true, 'ecuadorian' => true,
            'egypt' => true, 'egyptian' => true, 'el salvador' => true, 'salvadoran' => true, 'equatorial guinea' => true,
            'eritrea' => true, 'eritran' => true, 'estonia' => true, 'estonian' => true, 'eswatini' => true, 'ethiopia' => true, 'ethiopian' => true,
            'fiji' => true, 'fijian' => true, 'finland' => true, 'finnish' => true, 'france' => true, 'french' => true,
            'gabon' => true, 'gabonese' => true, 'gambia' => true, 'georgia' => true, 'georgian' => true,
            'germany' => true, 'german' => true, 'ghana' => true, 'ghanaian' => true, 'greece' => true, 'greek' => true,
            'grenada' => true, 'grenadian' => true, 'guatemala' => true, 'guatemalan' => true, 'guinea' => true, 'guinean' => true,
            'guinea-bissau' => true, 'guyana' => true, 'guyanese' => true, 'haiti' => true, 'haitian' => true,
            'honduras' => true, 'honduran' => true, 'hong kong' => true, 'hungary' => true, 'hungarian' => true, 'iceland' => true, 'icelandic' => true,
            'india' => true, 'indian' => true, 'indonesia' => true, 'indonesian' => true, 'iran' => true, 'iranian' => true,
            'iraq' => true, 'iraqi' => true, 'ireland' => true, 'irish' => true, 'israel' => true, 'israeli' => true,
            'italy' => true, 'italian' => true, 'ivory coast' => true, 'jamaica' => true, 'jamaican' => true,
            'japan' => true, 'japanese' => true, 'jordan' => true, 'jordanian' => true, 'kazakhstan' => true, 'kazakh' => true, 'korea' => true, 'korean' => true,
            'kenya' => true, 'kenyan' => true, 'kiribati' => true, 'kosovo' => true, 'kosovar' => true,
            'kuwait' => true, 'kuwaiti' => true, 'kyrgyzstan' => true, 'laos' => true, 'laotian' => true,
            'latvia' => true, 'latvian' => true, 'lebanon' => true, 'lebanese' => true, 'lesotho' => true, 'liberia' => true, 'liberian' => true,
            'libya' => true, 'liechtenstein' => true, 'lithuania' => true, 'lithuanian' => true, 'luxembourg' => true, 'macedonia' => true,
            'madagascar' => true, 'madagascan' => true, 'malawi' => true, 'malawian' => true, 'malaysia' => true, 'malaysian' => true,
            'maldives' => true, 'mali' => true, 'malian' => true, 'malta' => true, 'marshall islands' => true, 'mauritania' => true,
            'mauritius' => true, 'mauritian' => true, 'mexico' => true, 'mexican' => true, 'micronesia' => true, 'moldova' => true, 'moldovan' => true,
            'monaco' => true, 'mongolia' => true, 'mongolian' => true, 'montenegro' => true, 'montenegrin' => true, 'morocco' => true,
            'moroccan' => true, 'mozambique' => true, 'myanmar' => true, 'namibia' => true, 'namibian' => true,
            'nauru' => true, 'nepal' => true, 'nepalese' => true, 'netherlands' => true, 'dutch' => true, 'holland' => true,
            'new zealand' => true, 'new zealander' => true, 'nicaragua' => true, 'nicaraguan' => true, 'niger' => true, 'nigerian' => true,
            'nigeria' => true, 'north korea' => true, 'north korean' => true, 'north macedonia' => true, 'macedonian' => true, 'norway' => true, 'norwegian' => true,
            'oman' => true, 'omani' => true, 'pakistan' => true, 'pakistani' => true, 'palau' => true, 'palestine' => true, 'palestinian' => true,
            'panama' => true, 'panamanian' => true, 'papua new guinea' => true, 'paraguay' => true, 'paraguayan' => true,
            'peru' => true, 'peruvian' => true, 'philippines' => true, 'filipino' => true, 'poland' => true, 'polish' => true,
            'portugal' => true, 'portuguese' => true, 'puerto rico' => true, 'puerto rican' => true, 'qatar' => true, 'qatari' => true,
            'romania' => true, 'romanian' => true, 'russia' => true, 'russian' => true, 'rwanda' => true, 'rwandan' => true,
            'saint kitts and nevis' => true, 'saint lucia' => true, 'saint vincent and the grenadines' => true, 'samoa' => true, 'samoan' => true,
            'san marino' => true, 'sao tome and principe' => true, 'saudi arabia' => true, 'saudi' => true, 'senegal' => true, 'senegalese' => true,
            'serbia' => true, 'serbian' => true, 'seychelles' => true, 'sierra leone' => true, 'singapore' => true, 'singaporean' => true,
            'slovakia' => true, 'slovak' => true, 'slovenia' => true, 'slovenian' => true, 'solomon islands' => true, 'somalia' => true, 'somali' => true,
            'south africa' => true, 'south sudan' => true, 'south korea' => true, 'south korean' => true, 'spain' => true, 'spanish' => true,
            'sri lanka' => true, 'sudan' => true, 'sudanese' => true, 'suriname' => true, 'surinamese' => true, 'sweden' => true, 'swedish' => true,
            'switzerland' => true, 'swiss' => true, 'syria' => true, 'syrian' => true, 'taiwan' => true, 'taiwanese' => true,
            'tajikistan' => true, 'tanzania' => true, 'tanzanian' => true, 'thailand' => true, 'thai' => true, 'timor-leste' => true,
            'togo' => true, 'tonga' => true, 'tongan' => true, 'trinidad and tobago' => true, 'trinidadian' => true, 'tobagonian' => true,
            'tunisia' => true, 'tunisian' => true, 'turkey' => true, 'turkish' => true, 'turkmenistan' => true, 'tuvalu' => true,
            'uganda' => true, 'ugandan' => true, 'ukraine' => true, 'ukrainian' => true, 'united arab emirates' => true, 'uae' => true, 'emirati' => true,
            'united kingdom' => true, 'uk' => true, 'britain' => true, 'british' => true, 'united states' => true, 'usa' => true, 'us' => true, 'american' => true,
            'uruguay' => true, 'uruguayan' => true, 'uzbekistan' => true, 'uzbek' => true, 'vanuatu' => true, 'vanuatuan' => true,
            'vatican' => true, 'venezuela' => true, 'venezuelan' => true, 'vietnam' => true, 'vietnamese' => true,
            'yemen' => true, 'yemeni' => true, 'zambia' => true, 'zambian' => true, 'zimbabwe' => true, 'zimbabwean' => true,
        ];
    }
}
