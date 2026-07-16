<?php

namespace Database\Seeders;

use App\Models\Forum;
use App\Models\School;
use App\Support\Slug;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    /**
     * General/topic forum names. Slugs are generated to match the frontend.
     *
     * @var list<string>
     */
    private const GENERAL_FORUMS = [
        'Општи дискусии',
        'Државна матура',
        'Помош при учење',
        'Вештачка интелегенција',
        'Факултети',
        'Странски јазици',
        'Кариера и професии',
        'Студии во странство',
        'Ментално здравје',
        'Воннаставни активности',
        'Технологија и програмирање',
        'Забава и култура',
        'Спорт',
        'Социјални прашања',
        'Претстави се',
        'Слободни дискусии',
    ];

    public function run(): void
    {
        $this->seedGeneralForums();
        $this->seedSchoolForums();
    }

    private function seedGeneralForums(): void
    {
        foreach (self::GENERAL_FORUMS as $name) {
            $slug = Slug::make($name);

            Forum::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => 'Форум за темата „'.$name.'“.',
                    'type' => 'general',
                    'school_id' => null,
                    'imageUrl' => '/icons/'.$slug.'.svg',
                    'bannerUrl' => 'https://picsum.photos/seed/'.$slug.'/1200/300',
                    'threads_count' => 0,
                    'members_count' => 0,
                ],
            );
        }
    }

    /**
     * One forum per school, linked via `school_id`. The school (and its city)
     * remain the single source of truth for the name/location.
     */
    private function seedSchoolForums(): void
    {
        School::with('city')->each(function (School $school) {
            $slug = Slug::make($school->name.' '.$school->city->name);

            Forum::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'name' => $school->name,
                    'slug' => $slug,
                    'description' => 'Форум на '.$school->name.' ('.$school->city->name.').',
                    'type' => 'school',
                    'imageUrl' => '/icons/uciliste.svg',
                    'bannerUrl' => 'https://picsum.photos/seed/'.$slug.'/1200/300',
                    'threads_count' => 0,
                    'members_count' => 0,
                ],
            );
        });
    }
}
