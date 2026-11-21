<?php

namespace App\Livewire;

use App\Models\DemoSolicitud;
use Livewire\Component;

/** Formulario público de solicitud de demo de SIGO-NEXO. */
class SolicitarDemo extends Component
{
    public string $nombre = '';
    public string $email = '';
    public string $empresa = '';
    public string $telefono = '';
    public string $mensaje = '';

    public bool $enviado = false;

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'mensaje' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'Ingresá tu nombre.',
            'nombre.min' => 'El nombre es demasiado corto.',
            'email.required' => 'Ingresá tu correo.',
            'email.email' => 'Ingresá un correo válido.',
        ];
    }

    public function updated(string $campo): void
    {
        $this->validateOnly($campo);
    }

    public function enviar(): void
    {
        $this->validate();

        DemoSolicitud::create([
            'nombre' => $this->nombre,
            'email' => $this->email,
            'empresa' => $this->empresa ?: null,
            'telefono' => $this->telefono ?: null,
            'mensaje' => $this->mensaje ?: null,
            'ip' => request()->ip(),
        ]);

        $this->enviado = true;
        $this->reset(['nombre', 'email', 'empresa', 'telefono', 'mensaje']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.solicitar-demo');
    }
}
