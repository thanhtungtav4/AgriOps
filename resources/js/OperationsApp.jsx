import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import LoginPage from './pages/LoginPage';
import OperationsDashboard from './pages/OperationsDashboard';

function ProtectedRoute({ children }) {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="text-center">
                    <div className="w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p className="text-gray-600">Đang tải...</p>
                </div>
            </div>
        );
    }

    if (!isAuthenticated) {
        return <Navigate to="/operations/login" replace />;
    }

    return children;
}

function AppRoutes() {
    const { isAuthenticated } = useAuth();

    return (
        <Routes>
            <Route
                path="/operations/login"
                element={isAuthenticated ? <Navigate to="/operations" replace /> : <LoginPage />}
            />
            <Route
                path="/operations"
                element={
                    <ProtectedRoute>
                        <OperationsDashboard />
                    </ProtectedRoute>
                }
            />
            <Route
                path="/operations/*"
                element={
                    <ProtectedRoute>
                        <OperationsDashboard />
                    </ProtectedRoute>
                }
            />
            <Route path="*" element={<Navigate to="/operations" replace />} />
        </Routes>
    );
}

export default function OperationsApp() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <AppRoutes />
            </BrowserRouter>
        </AuthProvider>
    );
}