namespace {{ $namespace }};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * {{ $namespace }}\{{ $class }}
 *
@foreach ($columns as $column)
 * @property {{ $column['type'] . ' $' . $column['name'] }}{{ $column['comment'] ? " {$column['comment']}" : '' }}
@endforeach
 */
class {{ $class }} extends Model
{
    use HasFactory;
}
