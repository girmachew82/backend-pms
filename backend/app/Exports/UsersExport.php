<?php
namespace App\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{FromQuery, WithHeadings};

class UsersExport implements FromQuery, WithHeadings
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }


    public function query()
    {
       return $this->query;
    }
    public function headings(): array
    {
        return [
            "ID",
            "Name",
            "Email",
            "Created At"
        ];
    }
}
