<?php

namespace Lwekuiper\StatamicActivecampaign\Tests\Listeners;

use Illuminate\Support\Facades\Event;
use Lwekuiper\StatamicActivecampaign\Facades\FormConfig;
use Lwekuiper\StatamicActivecampaign\Listeners\AddFromSubmission;
use Lwekuiper\StatamicActiveCampaign\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Form;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class AddFromSubmissionTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_should_handle_submission_created_event()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $event = new SubmissionCreated($submission);

        $this->mock(AddFromSubmission::class)->shouldReceive('handle')->with($event)->once();

        Event::dispatch($event);
    }

    #[Test]
    public function it_returns_true_when_consent_field_is_not_configured()
    {
        $listener = new AddFromSubmission();

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_configured_consent_field_is_false()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['consent' => false]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $hasConsent = $listener->hasConsent();

        $this->assertFalse($hasConsent);
    }

    #[Test]
    public function it_returns_true_when_configured_consent_field_is_true()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();
        $submission->data(['consent' => true]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->consentField('consent');
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $hasConsent = $listener->hasConsent();

        $this->assertTrue($hasConsent);
    }

    #[Test]
    public function it_returns_false_when_form_config_is_missing()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $listener = new AddFromSubmission($submission->data());

        $hasFormConfig = $listener->hasFormConfig($submission);

        $this->assertFalse($hasFormConfig);
    }

    #[Test]
    public function it_returns_true_when_form_config_is_present()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->save();

        $listener = new AddFromSubmission($submission->data());

        $hasFormConfig = $listener->hasFormConfig($submission);

        $this->assertTrue($hasFormConfig);
    }

    #[Test]
    public function it_correctly_uses_email_field_from_config()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'custom_email_field' => 'john@example.com',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('custom_email_field');
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $email = $listener->getEmail();

        $this->assertEquals('john@example.com', $email);
    }

    #[Test]
    public function it_correctly_prepares_merge_data_for_sync_contact()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'custom_field' => 'Custom Value',
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->consentField('consent')->listId(1)->tagId(1);
        $formConfig->mergeFields([
            ['statamic_field' => 'email', 'activecampaign_field' => 'email'],
            ['statamic_field' => 'first_name', 'activecampaign_field' => 'firstName'],
            ['statamic_field' => 'last_name', 'activecampaign_field' => 'lastName'],
            ['statamic_field' => 'custom_field', 'activecampaign_field' => 'customField'],
        ]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $reflectionMethod = new ReflectionMethod(AddFromSubmission::class, 'getMergeData');
        $reflectionMethod->setAccessible(true);
        $mergeData = $reflectionMethod->invoke($listener);

        $this->assertEquals([
            'email' => 'john@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'fieldValues' => [
                [
                    'field' => 'customField',
                    'value' => 'Custom Value',
                ],
            ],
        ], $mergeData);
    }

    #[Test]
    public function it_handles_array_fields()
    {
        $form = tap(Form::make('contact_us')->title('Contact Us'))->save();
        $submission = $form->makeSubmission();

        $submission->data([
            'email' => 'john@example.com',
            'interests' => ['Sports', 'Music', 'Reading'],
            'skills' => ['PHP', '', 'JavaScript', null, 'Laravel'],
            'empty_array' => [],
            'null_values_only' => [null, '', null],
            'mixed_empty' => ['Valid', '', null, 'Also Valid'],
            'empty_string' => '',
            'null_value' => null,
        ]);

        $formConfig = FormConfig::make()->form($form)->locale('default');
        $formConfig->emailField('email')->listId(1);
        $formConfig->mergeFields([
            ['statamic_field' => 'email', 'activecampaign_field' => 'email'],
            ['statamic_field' => 'first_name', 'activecampaign_field' => 'firstName'],
            ['statamic_field' => 'last_name', 'activecampaign_field' => 'lastName'],
            ['statamic_field' => 'interests', 'activecampaign_field' => 'interests'],
            ['statamic_field' => 'skills', 'activecampaign_field' => 'skills'],
            ['statamic_field' => 'empty_array', 'activecampaign_field' => 'empty_field'],
            ['statamic_field' => 'null_values_only', 'activecampaign_field' => 'null_field'],
            ['statamic_field' => 'mixed_empty', 'activecampaign_field' => 'mixed_field'],
            ['statamic_field' => 'empty_string', 'activecampaign_field' => 'empty_string_field'],
            ['statamic_field' => 'null_value', 'activecampaign_field' => 'null_field'],
        ]);
        $formConfig->save();

        $listener = new AddFromSubmission();
        $listener->hasFormConfig($submission);

        $reflectionMethod = new ReflectionMethod(AddFromSubmission::class, 'getMergeData');
        $reflectionMethod->setAccessible(true);
        $mergeData = $reflectionMethod->invoke($listener);

        $this->assertEquals([
            'email' => 'john@example.com',
            'fieldValues' => [
                [
                    'field' => 'interests',
                    'value' => 'Sports, Music, Reading',
                ],
                [
                    'field' => 'skills',
                    'value' => 'PHP, JavaScript, Laravel',
                ],
                [
                    'field' => 'mixed_field',
                    'value' => 'Valid, Also Valid',
                ],
            ],
        ], $mergeData);
    }
}
