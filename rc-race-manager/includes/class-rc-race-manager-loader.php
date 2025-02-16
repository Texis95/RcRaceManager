<?php
if (!defined('ABSPATH')) {
    exit;
}

class RC_Race_Manager_Loader {
    protected $actions;
    protected $filters;

    public function __construct() {
        error_log('RC Race Manager: Inizializzazione loader');
        $this->actions = array();
        $this->filters = array();
    }

    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        error_log(sprintf(
            'RC Race Manager: Aggiunto hook - %s, Callback: %s, Priorità: %d',
            $hook,
            is_array($callback) ? get_class($component) . '->' . $callback : $callback,
            $priority
        ));

        return $hooks;
    }

    public function run() {
        error_log('RC Race Manager: Avvio esecuzione hooks');

        // Verifica che le funzioni WordPress necessarie esistano
        if (!function_exists('add_action') || !function_exists('add_filter')) {
            error_log('RC Race Manager: Errore - Funzioni WordPress mancanti');
            return;
        }

        try {
            foreach ($this->filters as $hook) {
                add_filter(
                    $hook['hook'],
                    array($hook['component'], $hook['callback']),
                    $hook['priority'],
                    $hook['accepted_args']
                );
            }

            foreach ($this->actions as $hook) {
                add_action(
                    $hook['hook'],
                    array($hook['component'], $hook['callback']),
                    $hook['priority'],
                    $hook['accepted_args']
                );
            }

            error_log('RC Race Manager: Hooks registrati con successo');
        } catch (Exception $e) {
            error_log('RC Race Manager: Errore durante la registrazione degli hooks - ' . $e->getMessage());
        }
    }
}