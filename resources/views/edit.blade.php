@extends('statamic::layout')

@section('title', __('Edit ActiveCampaign'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('activecampaign.index'),
        'title' => 'ActiveCampaign'
    ])

    <activecampaign-publish-form
        title="{{ $title }}"
        initial-action="{{ $action }}"
        initial-delete-url="{{ $deleteUrl }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-meta='{{ empty($meta) ? '{}' : json_encode($meta) }}'
        :initial-values='{{ empty($values) ? '{}' : json_encode($values) }}'
        :initial-localizations="{{ empty($localizations) ? '{}' : json_encode($localizations) }}"
        initial-site="{{ $site }}"
        listing-url="{{ cp_route('activecampaign.index') }}"
    ></activecampaign-publish-form>

@stop
