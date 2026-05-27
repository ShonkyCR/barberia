<?php
require_once __DIR__ . '/vendor/autoload.php';

class GoogleCalendarHelper
{
    private $client;
    private $service;

    public function __construct()
    {
        $keyFilePath = __DIR__ . '/service-account.json';

        if (!file_exists($keyFilePath)) {
            throw new Exception("El archivo service-account.json no existe. Por favor, configúralo.");
        }

        $this->client = new Google_Client();
        $this->client->setAuthConfig($keyFilePath);
        $this->client->addScope(Google_Service_Calendar::CALENDAR);
        $this->client->setAccessType('offline');

        $this->service = new Google_Service_Calendar($this->client);
    }

    /**
     * Crea un evento en el calendario especificado.
     * 
     * @param string $calendarId El ID del calendario (email del barbero o 'primary')
     * @param string $summary Título del evento
     * @param string $description Descripción del evento
     * @param string $fecha YYYY-MM-DD
     * @param string $hora HH:MM
     * @param int $durationMinutos Duración en minutos (default 30)
     * @return string ID del evento creado
     */
    public function crearEvento($calendarId, $summary, $description, $fecha, $hora, $durationMinutos = 30)
    {
        $startDateTime = $fecha . 'T' . $hora . ':00';
        $endDateTime = date('Y-m-d\TH:i:s', strtotime($startDateTime . " + $durationMinutos minutes"));

        // Ajustar timezone si es necesario, aquí asumimos la del sistema o UTC
        // Lo ideal es que coincida con la del calendario de Google

        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'description' => $description,
            'start' => [
                'dateTime' => $startDateTime,
                'timeZone' => 'America/Costa_Rica', // Ajustar según ubicación
            ],
            'end' => [
                'dateTime' => $endDateTime,
                'timeZone' => 'America/Costa_Rica',
            ],
        ]);

        try {
            $event = $this->service->events->insert($calendarId, $event);
            return $event->getId();
        } catch (Exception $e) {
            error_log("Error al crear: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Elimina un evento del calendario.
     * 
     * @param string $calendarId
     * @param string $eventId
     * @return bool
     */
    public function eliminarEvento($calendarId, $eventId)
    {
        if (empty($eventId))
            return false;
        try {
            $this->service->events->delete($calendarId, $eventId);
            return true;
        } catch (Exception $e) {
            error_log("Error al eliminar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un evento existente en el calendario.
     * 
     * @param string $calendarId
     * @param string $eventId
     * @param string $summary
     * @param string $description
     * @param string $fecha
     * @param string $hora
     * @param int $durationMinutos
     * @return bool
     */
    public function actualizarEvento($calendarId, $eventId, $summary, $description, $fecha, $hora, $durationMinutos = 30)
    {
        if (empty($eventId)) return false;

        $startDateTime = $fecha . 'T' . $hora . ':00';
        $endDateTime = date('Y-m-d\TH:i:s', strtotime($startDateTime . " + $durationMinutos minutes"));

        try {
            $event = $this->service->events->get($calendarId, $eventId);
            $event->setSummary($summary);
            $event->setDescription($description);
            
            $start = new Google_Service_Calendar_EventDateTime();
            $start->setDateTime($startDateTime);
            $start->setTimeZone('America/Costa_Rica');
            $event->setStart($start);

            $end = new Google_Service_Calendar_EventDateTime();
            $end->setDateTime($endDateTime);
            $end->setTimeZone('America/Costa_Rica');
            $event->setEnd($end);

            $this->service->events->update($calendarId, $eventId, $event);
            return true;
        } catch (Exception $e) {
            error_log("Error al actualizar evento Google: " . $e->getMessage());
            return false;
        }
    }
}
?>