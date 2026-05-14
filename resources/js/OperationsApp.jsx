import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import LoginPage from './pages/LoginPage';
import OperationsDashboard from './pages/OperationsDashboard';
import OperationsLayout from './components/operations/OperationsLayout';
import PlanningCalculator from './pages/operations/PlanningCalculator';
import ProductionPlansPage from './pages/operations/ProductionPlansPage';
import BatchesPage from './pages/operations/BatchesPage';
import BatchDetailPage from './pages/operations/BatchDetailPage';
import CreateBatchPage from './pages/operations/CreateBatchPage';
import FulfillmentFlow from './pages/operations/fulfillment/FulfillmentFlow';
import TasksPage from './pages/operations/tasks/TasksPage';
import TaskDetailPage from './pages/operations/tasks/TaskDetailPage';
import SupplyContractsPage from './pages/operations/supply/SupplyContractsPage';
import SupplyDemandsPage from './pages/operations/supply/SupplyDemandsPage';
import SafetyPage from './pages/operations/safety/SafetyPage';
import FinancePage from './pages/operations/finance/FinancePage';
import PostSeasonReviewsPage from './pages/operations/reviews/PostSeasonReviewsPage';

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
                        <OperationsLayout />
                    </ProtectedRoute>
                }
            >
                <Route index element={<OperationsDashboard />} />
                <Route path="planning" element={<PlanningCalculator />} />
                <Route path="plans" element={<ProductionPlansPage />} />
                <Route path="supply/contracts" element={<SupplyContractsPage />} />
                <Route path="supply/demands" element={<SupplyDemandsPage />} />
                <Route path="batches" element={<BatchesPage />} />
                <Route path="batches/create" element={<CreateBatchPage />} />
                <Route path="batches/:id" element={<BatchDetailPage />} />
                <Route path="fulfillment" element={<FulfillmentFlow />} />
                <Route path="tasks" element={<TasksPage />} />
                <Route path="tasks/:id" element={<TaskDetailPage />} />
                <Route path="safety" element={<SafetyPage />} />
                <Route path="finance" element={<FinancePage />} />
                <Route path="reviews" element={<PostSeasonReviewsPage />} />
            </Route>
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
