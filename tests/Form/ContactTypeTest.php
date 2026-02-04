<?php

namespace App\Tests\Form;

use App\Entity\Lead;
use App\Form\ContactType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

class ContactTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Jonas',
            'email' => 'jonas@example.com',
            'subject' => 'Test',
            'message' => 'Ceci est un message de test avec plus de 10 caractères.',
        ];

        $model = new Lead();
        $form = $this->factory->create(ContactType::class, $model, [
            'enable_captcha' => false,
        ]);

        $expected = new Lead();
        $expected->setName('Jonas');
        $expected->setEmail('jonas@example.com');
        $expected->setSubject('Test');
        $expected->setMessage('Ceci est un message de test avec plus de 10 caractères.');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected->getName(), $model->getName());
        $this->assertEquals($expected->getEmail(), $model->getEmail());
        $this->assertEquals($expected->getSubject(), $model->getSubject());
        $this->assertEquals($expected->getMessage(), $model->getMessage());
    }
}