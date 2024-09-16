<?php
require_once 'DatabaseConnection.php';

class BookingCalendar extends DatabaseConnection
{
    public function buildCalendar($month, $year)
    {
        $startOfMonth = new DateTime("$year-$month-01");
        $endOfMonth = (clone $startOfMonth)->modify('last day of this month');
        $availableSlots = [];

        $currentDate = clone $startOfMonth;
        while ($currentDate <= $endOfMonth) {
            $formattedDate = $currentDate->format('Y-m-d');
            $availableSlots[$formattedDate] = 6; // Assume 6 slots per day
            $currentDate->modify('+1 day');
        }

        // Verify if this query is returning the correct results
        $stmt = $this->getConnection()->prepare("SELECT date, COUNT(*) as booked_count FROM bookings WHERE MONTH(date) = ? AND YEAR(date) = ? GROUP BY date");

        $stmt->bind_param('ii', $month, $year);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $date = $row['date'];
                $bookedCount = $row['booked_count'];
                $availableSlots[$date] = max(0, 6 - $bookedCount); // Subtract booked slots from total slots
            }
            $stmt->close();
        }

        return [
            'availableSlots' => $availableSlots
        ];
    }

    public function checkSlots($date)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM bookings WHERE date = ?");
        $stmt->bind_param('s', $date);
        $totalBookings = 0;

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $totalBookings = $result->num_rows;
            $stmt->close();
        }

        return $totalBookings;
    }

    public function getBookings($date)
    {
        $stmt = $this->getConnection()->prepare("SELECT * FROM bookings WHERE date = ?");
        $stmt->bind_param('s', $date);
        $bookings = [];

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
            $stmt->close();
        }

        return $bookings;
    }

    public function addBooking($name, $email, $phone, $vehicleModel, $vehicleNumber, $timeslot, $date)
    {
        $stmt = $this->getConnection()->prepare("INSERT INTO bookings (name, email, phone, vehicle_model, vehicle_number, timeslot, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $name, $email, $phone, $vehicleModel, $vehicleNumber, $timeslot, $date);
        $stmt->execute();
        $stmt->close();
    }

    public function getTimeslotStatus($date)
    {
        $timeslots = [
            "09:00AM-10:30AM",
            "10:30AM-12:00PM",
            "12:00PM-01:30PM",
            "01:30PM-03:00PM",
            "03:00PM-04:30PM",
            "04:30PM-06:00PM"
        ];

        $status = array_fill_keys($timeslots, 'available');

        $stmt = $this->getConnection()->prepare("SELECT timeslot FROM bookings WHERE date = ?");
        $stmt->bind_param('s', $date);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $status[$row['timeslot']] = 'booked';
            }
            $stmt->close();
        }

        return $status;
    }
}