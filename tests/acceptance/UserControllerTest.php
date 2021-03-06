<?php

namespace Tests\Acceptance;

use Tests\AcceptanceTestCase;
use App\Entities\User;

class UserControllerTest extends AcceptanceTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->createStaff();
    }
    
    public function testView()
    {
        $this->visit('/')->see('mdl-navigation__link" href="'.$this->baseUrl.'/user">');
    
        $this->visit('/user')
            ->see('<i class="material-icons">filter_list</i>')
        ;
    }
    
    public function testCreate()
    {
        $this->visit('/user')->see('<a href="'.$this->baseUrl.'/user/create');
        
        $this->visit('/user/create');
    
        $idOption = $this->crawler->filterXPath("//select[@name='role_id']/option[5]")->attr('value');
    
        $this->type('Nome Usuario Teste', 'name')
            ->type('teste@alientronics.com.br', 'email')
            ->type('admin', 'password')
            ->select($idOption, 'role_id')
            ->type('Brasil', 'country')
            ->type('RS', 'state')
            ->type('Porto Alegre', 'city')
            ->type('Adress', 'address')
            ->type('(99) 9999-9999', 'phone')
            ->press('Enviar')
            ->seePageIs('/user')
        ;
    
        $this->seeInDatabase('users', ['name' => 'Nome Usuario Teste', 'email' => 'teste@alientronics.com.br']);
        $this->seeInDatabase('contacts', ['name' => 'Nome Usuario Teste',
                                            'country' => 'Brasil',
                                            'state' => 'RS',
                                            'city' => 'Porto Alegre',
                                            'address' => 'Adress',
                                            'phone' => '(99) 9999-9999'
        ]);
        $this->seeInDatabase('role_user', ['role_id' => '5', 'user_id' => User::all()->last()['id']]);
    }
    
    public function testUpdate()
    {
        $this->visit('/user/'.User::all()->last()['id'].'/edit');
        
        $idOption = $this->crawler->filterXPath("//select[@name='role_id']/option[3]")->attr('value');
            
        $this->type('Nome Usuario Editado', 'name')
            ->type('emaileditado@usuario.com', 'email')
            ->type('654321', 'password')
            ->select($idOption, 'role_id')
            ->type('Brasil2', 'country')
            ->type('RS2', 'state')
            ->type('Porto Alegre2', 'city')
            ->type('Adress2', 'address')
            ->type('(99) 9999-9998', 'phone')
            ->press('Enviar')
        ;
        
        $this->seeInDatabase('users', ['name' => 'Nome Usuario Editado', 'email' => 'emaileditado@usuario.com']);
        $this->seeInDatabase('contacts', ['name' => 'Nome Usuario Editado',
                                            'country' => 'Brasil2',
                                            'state' => 'RS2',
                                            'city' => 'Porto Alegre2',
                                            'address' => 'Adress2',
                                            'phone' => '(99) 9999-9998'
        ]);
        $this->seeInDatabase('role_user', ['role_id' => '3', 'user_id' => User::all()->last()['id']]);
    }
    
    public function testDelete()
    {
        $userDelete = User::all()->last();
        $this->seeInDatabase('users', ['email' => $userDelete->email]);
        $this->visit('/user/destroy/'.$userDelete->id);
        $this->seeIsSoftDeletedInDatabase('users', ['email' => $userDelete->email]);
    }
    
    public function testProfile()
    {
        $this->notSeeInDatabase(
            'users',
            ['name' => 'Administrator2',
            'email' => 'admin2@alientronics.com.br',
            'language' => 'en']
        );

        $this->visit('/profile');
        
        $this->type('Administrator2', 'name')
            ->type('admin2@alientronics.com.br', 'email')
            ->select('en', 'language')
            ->press('Enviar')
        ;
        $this->seeInDatabase(
            'users',
            ['name' => 'Administrator2',
            'email' => 'admin2@alientronics.com.br',
            'language' => 'en']
        );
    }
    
    public function testErrors()
    {
        $this->visit('/user/create')
            ->press('Enviar')
            ->seePageIs('/user/create')
            ->see('de um valor para o campo nome.</span>')
        ;
    }
    
    public function testFilters()
    {
        $this->visit('/user')
            ->type('Administrator', 'name')
            ->type('admin@alientronics.com.br', 'email')
            ->type('Administrator', 'contact_id')
            ->type('Company', 'company_id')
            ->press('Buscar')
            ->see('Administrator</div>')
            ->see('admin@alientronics.com.br</div>')
            ->see('Administrator</div>')
            ->see('Company</div>')
        ;
    }
    
    public function testSort()
    {
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=id-desc')
            ->see('mode_edit</i>');
        
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=id-asc')
            ->see('mode_edit</i>');
        
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=name-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=name-asc')
            ->see('mode_edit</i>');
        
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=email-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=email-asc')
            ->see('mode_edit</i>');
        
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=contact-id-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=contact-id-asc')
            ->see('mode_edit</i>');
        
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=company-id-desc')
            ->see('mode_edit</i>');
            
        $this->visit('/user?id=&name=&email=&contact-id=&company-id=&sort=company-id-asc')
            ->see('mode_edit</i>');
    }
}
