<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nail Architect - Admin Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            background-color: #F2E9E9;
            display: flex;
        }
        
        .sidebar {
            width: 250px;
            background-color:#E8D7D0;
            height: 100vh;
            padding: 25px 0;
            position: fixed;
            overflow-y: auto;
        }
        
        .logo-container {
            padding: 0 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #e0c5b7;
            position: relative;
            overflow: hidden;
        }
        
        .logo::after {
            content: "";
            position: absolute;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #dcdcdc;
            right: -8px;
            bottom: -8px;
        }
        
        .admin-title {
            margin-left: 15px;
            font-weight: bold;
            font-size: 18px;
        }
        
        .nav-menu {
            margin-top: 20px;
        }
        
        .menu-section {
            margin-bottom: 10px;
            padding: 0 20px;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .menu-item:hover {
            background-color: #D9BBB0;
        }
        
        .menu-item.active {
            background-color: #D9BBB0;
        }
        
        .menu-item.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: #333;
        }
        
        .menu-icon {
            width: 20px;
            margin-right: 10px;
            text-align: center;
            font-size: 16px;
        }
        
        .menu-text {
            font-size: 14px;
        }
        
        .content-wrapper {
            flex: 1;
            margin-left: 250px;
            padding: 25px;
            overflow-y: auto;
        }
        
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: bold;
        }
        
        .admin-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-search {
            position: relative;
        }
        
        .search-input {
            padding: 10px 15px 10px 40px;
            border: none;
            border-radius: 25px;
            background-color: #dcdcdc;
            width: 250px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            background-color: #c0c0c0;
            width: 300px;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }
        
        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e0c5b7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
        }
        
        .admin-name {
            font-size: 14px;
            font-weight: bold;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .stat-card {
            background-color: #E8D7D0;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .stat-title {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .stat-change {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .change-positive {
            color: #2e7d32;
        }
        
        .change-negative {
            color: #c62828;
        }
        
        .content-section {
            background-color: #E8D7D0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            animation: fadeIn 0.7s ease-out forwards;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
        }
        
        .section-controls {
            display: flex;
            gap: 15px;
        }
        
        .control-button {
            padding: 8px 16px;
            border-radius: 20px;
            background-color: #D9BBB0;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .control-button:hover {
            background-color: #ae9389;
        }
        
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 20px;
            border-bottom: 1px solid #c0c0c0;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            position: relative;
        }
        
        .tab.active {
            font-weight: bold;
        }
        
        .tab.active::after {
            content: "";
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #333;
        }
        
        .tab:hover {
            background-color: #D9BBB0;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .appointments-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .appointments-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #c0c0c0;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .appointments-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        
        .appointments-table tr:hover {
            background-color: #D9BBB0;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-confirmed {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .status-pending {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .status-cancelled {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .status-completed {
            background-color: #e0e0e0;
            color: #616161;
        }
        
        .payment-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .payment-paid {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        
        .payment-unpaid {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .payment-deposit {
            background-color: #bbdefb;
            color: #1565c0;
        }
        
        .action-cell {
            display: flex;
            gap: 8px;
        }
        
        .action-button {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }
        
        .action-button:hover {
            background-color: #c0c0c0;
        }
        
        .view-button {
            background-color: #bbdefb;
            color: #1565c0;
        }
        
        .edit-button {
            background-color: #fff9c4;
            color: #f57f17;
        }
        
        .cancel-button {
            background-color: #ffcdd2;
            color: #c62828;
        }
        
        .page-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        
        .page-info {
            font-size: 14px;
            color: #666;
        }
        
        .page-buttons {
            display: flex;
            gap: 10px;
        }
        
        .page-button {
            padding: 8px 12px;
            border-radius: 8px;
            background-color: #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 12px;
        }
        
        .page-button:hover {
            background-color: #c0c0c0;
        }
        
        .page-button.active {
            background-color: #c0c0c0;
            font-weight: bold;
        }
        
        .messages-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message-card {
            background-color: #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        
        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .message-sender {
            font-weight: bold;
            font-size: 14px;
        }
        
        .message-date {
            font-size: 12px;
            color: #666;
        }
        
        .message-subject {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .message-preview {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
        }
        
        .two-column-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .calendar-container {
            margin-top: 20px;
            min-height: 600px;
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .calendar-month {
            font-size: 18px;
            font-weight: bold;
        }
        
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        
        .calendar-nav-button {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .calendar-nav-button:hover {
            background-color: #c0c0c0;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .calendar-weekday {
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 5px;
            font-weight: bold;
        }
        
        .calendar-day {
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .calendar-day:hover {
            background-color: #e0e0e0;
        }
        
        .calendar-day.other-month {
            color: #ccc;
        }
        
        .calendar-day.today {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .calendar-day.has-events::after {
            content: "";
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: #333;
        }
        
        .calendar-day.selected {
            background-color: #c0c0c0;
        }
        
        .day-events-list {
            margin-top: 20px;
        }
        
        .day-events-header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .event-item {
            background-color: #f0f0f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .event-item:hover {
            background-color: #e0e0e0;
        }
        
        .event-time {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .event-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .event-client {
            font-size: 12px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: #f8f8f8;
            border-radius: 15px;
            padding: 30px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-title {
            font-size: 20px;
            font-weight: bold;
        }
        
        .close-modal {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
        }
        
        .close-modal:hover {
            background-color: #c0c0c0;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-group {
            margin-bottom: 20px;
        }
        
        .detail-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 14px;
            font-weight: bold;
        }
        
        .inspo-images {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .inspo-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            object-fit: cover;
        }
        
        .modal-section {
            margin-bottom: 25px;
        }
        
        .modal-section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .notification-item {
            background-color: #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #c0c0c0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .notification-time {
            font-size: 12px;
            color: #666;
        }
        
        /* Responsive styles */
        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .two-column-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            
            .content-wrapper {
                margin-left: 80px;
            }
            
            .admin-title, .menu-text, .menu-section {
                display: none;
            }
            
            .menu-item {
                justify-content: center;
                padding: 15px 0;
            }
            
            .menu-icon {
                margin-right: 0;
                font-size: 20px;
            }
            
            .logo-container {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .admin-controls {
                width: 100%;
                justify-content: space-between;
            }
            
            .search-input {
                width: 200px;
            }
            
            .search-input:focus {
                width: 230px;
            }
        }
        
        @media (max-width: 576px) {
            .content-wrapper {
                padding: 15px;
            }
            
            .sidebar {
                width: 60px;
            }
            
            .content-wrapper {
                margin-left: 60px;
            }
            
            .admin-name {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo"></div>
            <div class="admin-title">Admin</div>
        </div>
        
        <div class="nav-menu">
            <div class="menu-section">Main</div>
            
            <div class="menu-item active">
                <div class="menu-icon">📊</div>
                <div class="menu-text">Dashboard</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-icon">📅</div>
                <div class="menu-text">Appointments</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-icon">👥</div>
                <div class="menu-text">Clients</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-icon">💌</div>
                <div class="menu-text">Messages</div>
            </div>
            
            <div class="menu-section">System</div>
            
            <div class="menu-item">
                <div class="menu-icon">↩️</div>
                <div class="menu-text">Logout</div>
            </div>
        </div>
    </div>
    
    <div class="content-wrapper">
        <div class="top-bar">
            <div class="page-title">Dashboard</div>
        
        </div>
        
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-title">Total Appointments</div>
                <div class="stat-value">248</div>
                <div class="stat-change change-positive">↑ 12% from last month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Appointments Today</div>
                <div class="stat-value">18</div>
                <div class="stat-change change-positive">↑ 5% from yesterday</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Pending Appointments</div>
                <div class="stat-value">24</div>
                <div class="stat-change change-negative">↑ 8% from last week</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-title">Total Revenue</div>
                <div class="stat-value">$5,842</div>
                <div class="stat-change change-positive">↑ 15% from last month</div>
            </div>
        </div>
        
        <div class="two-column-grid">
            <div class="content-section">
                <div class="section-header">
                    <div class="section-title">Upcoming Appointments</div>
                    
                    <div class="section-controls">
                        <div class="control-button">Export</div>
                        <div class="control-button">New Appointment</div>
                    </div>
                </div>
                
                <div class="tabs">
                    <div class="tab active" data-tab="all">All</div>
                    <div class="tab" data-tab="confirmed">Confirmed</div>
                    <div class="tab" data-tab="pending">Pending</div>
                    <div class="tab" data-tab="cancelled">Cancelled</div>
                </div>
                
                <div class="tab-content active" id="all-tab">
                    <table class="appointments-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>Gel Manicure</td>
                                <td>Apr 8, 2025 - 2:00 PM</td>
                                <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                <td><span class="payment-badge payment-deposit">Deposit</span></td>
                                <td class="action-cell">
                                    <div class="action-button view-button" data-id="a1">👁️</div>
                                    <div class="action-button edit-button" data-id="a1">✏️</div>
                                    <div class="action-button cancel-button" data-id="a1">✖️</div>
                                </td>
                            </tr>
                            <tr>
                                <td>Michael Smith</td>
                                <td>Classic Pedicure</td>
                                <td>Apr 8, 2025 - 3:30 PM</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td><span class="payment-badge payment-unpaid">Unpaid</span></td>
                                <td class="action-cell">
                                    <div class="action-button view-button" data-id="a2">👁️</div>
                                    <div class="action-button edit-button" data-id="a2">✏️</div>
                                    <div class="action-button cancel-button" data-id="a2">✖️</div>
                                </td>
                            </tr>
                            <tr>
                                <td>Emma Wilson</td>
                                <td>Nail Art Design</td>
                                <td>Apr 8, 2025 - 4:15 PM</td>
                                <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                <td><span class="payment-badge payment-paid">Paid</span></td>
                                <td class="action-cell">
                                    <div class="action-button view-button" data-id="a3">👁️</div>
                                    <div class="action-button edit-button" data-id="a3">✏️</div>
                                    <div class="action-button cancel-button" data-id="a