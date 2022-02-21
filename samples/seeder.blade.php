@php($name=$table->name->singular()->studly())
@cbSaveAs(base_path("generatedcode/database/seeders/".$name.'Seeder.php'))
{{-- @cbSaveAs(base_path("database/seeders/".$name.'Seeder.php')) --}}
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\{{$name}};
class {{$name}}Seeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    {{$name}}::factory()->count(50)->create();
  }
}
