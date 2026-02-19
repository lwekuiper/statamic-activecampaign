@extends('statamic::layout')

@section('title', __('Configure ActiveCampaign'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('activecampaign.index'),
        'title' => 'ActiveCampaign'
    ])

    <activecampaign-publish-form
        title="{{ $title }}"
        initial-action="{{ $action }}"
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-meta='{{ empty($meta) ? '{}' : json_encode($meta) }}'
        :initial-values='{{ empty($values) ? '{}' : json_encode($values) }}'
        :initial-localizations="[]"
        initial-site=""
    ></activecampaign-publish-form>

@stop
