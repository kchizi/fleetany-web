<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Repositories\CompanyRepository;
use App\Entities\Company;
use App\Entities\Type;
use Illuminate\Support\Facades\Auth;

class CompanyRepositoryEloquent extends BaseRepository implements CompanyRepository
{

    protected $rules = [
        'name'      => 'min:3|required',
        'api_token'      => 'min:3|required',
        ];

    public function model()
    {
        return Company::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
    public function results($filters = array())
    {
        $companies = $this->scopeQuery(function ($query) use ($filters) {
            
            $query = $query->select('companies.*', 'contacts.city', 'contacts.country')
                        ->leftJoin('contacts', 'companies.contact_id', '=', 'contacts.id');
            
            if (!empty($filters['name'])) {
                $query = $query->where('companies.name', 'like', '%'.$filters['name'].'%');
            }
            if (!empty($filters['city'])) {
                $query = $query->where('contacts.city', 'like', '%'.$filters['city'].'%');
            }
            if (!empty($filters['country'])) {
                $query = $query->where('contacts.country', 'like', '%'.$filters['country'].'%');
            }

            if ($filters['sort'] == 'city' || $filters['sort'] == 'country') {
                $sort = 'contacts.'.$filters['sort'];
            } else {
                $sort = 'companies.'.$filters['sort'];
            }
            
            $query = $query->orderBy($sort, $filters['order']);
            
            return $query;
        })->paginate($filters['paginate']);
        
        return $companies;
    }
    
    public function setInputs($inputs, $user = null)
    {
        $inputs['company_id'] = Auth::user()['company_id'];
        $typeId = Type::where('entity_key', 'contact')
                                            ->where('name', 'detail')
                                            ->where('company_id', $inputs['company_id'])
                                            ->first();
        $inputs['contact_type_id'] = $typeId->id;
        return $inputs;
    }
    
    public function hasReferences($idCompany)
    {
        $company = $this->find($idCompany);
        $countReferences = $company->contacts()->count();
        $countReferences += $company->entries()->count();
        $countReferences += $company->models()->count();
        $countReferences += $company->trips()->count();
        $countReferences += $company->types()->count();
        $countReferences += $company->usersCompany()->count();
        $countReferences += $company->usersPendingCompany()->count();
        $countReferences += $company->vehicles()->count();
        
        if ($countReferences > 0) {
            return true;
        }
        return false;
    }
    
    public static function getCompanies()
    {
        $companies = Company::lists('name', 'id');
        return $companies;
    }
}
