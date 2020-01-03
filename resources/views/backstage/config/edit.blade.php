{{-- EXTEND --}}
    @extends('backstage.config.layout')

{{-- VARS --}}
    @section('title', 'Edit config')

{{-- FORM --}}
    @section('form_method', 'POST')
    @section('form_action', route('config.update'))
    @section('form_laravelCsrf', csrf_field())
    @section('form_laravelMethod', method_field('PUT'))
    @section('form_disabled', '')

    @section('HTML-btnAction')
        <button class="btn btn-success"
            type="submit"
            form="form"
            @click="isValidForm()"
        >Guardar</button>
    @endsection

    @section('chips_value', $config->config['chips'])

    @section('commission_value', $config->config['commission'])

    @section('keep_complete_value', $config->config['keep_complete'])
