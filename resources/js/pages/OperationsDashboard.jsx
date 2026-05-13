import React, { useState, useEffect, useMemo } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';

const PRIORITY_ORDER = { urgent: 1, high: 2, normal: 3, low: 4 };
const SEVERITY_ORDER = { critical: 1, warning: 2, info: 3 };
const ACTIVE_BATCH_STATUSES = ['soil_prep', 'planting', 'growing', 'flowering', 'fruiting', 'harvesting', 'active'];

const batchCode = batch => batch.code || batch.batch_code || '-';
const taskBatch = task => task.planting_batch || task.plantingBatch || task.batch;
const taskDueDate = task => task.planned_due_date || task.due_date;

function StatsBar({ data }) {
    const criticalCount = data.alerts.filter(a => a.severity === 'critical').length;
    const warningCount = data.alerts.filter(a => a.severity === 'warning').length;
    const overdueCount = data.tasks.filter(t => {
        const due = taskDueDate(t);
        return due && new Date(due) < new Date() && t.status !== 'done' && t.status !== 'cancelled';
    }).length;
    const activeBatches = data.batches.filter(b => ACTIVE_BATCH_STATUSES.includes(b.status)).length;

    return (
        <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
            <div className="bg-red-50 border border-red-200 rounded-lg p-3">
                <div className="text-xs text-red-600 font-medium uppercase">Nghiêm trọng</div>
                <div className="text-2xl font-bold text-red-700">{criticalCount}</div>
            </div>
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <div className="text-xs text-yellow-700 font-medium uppercase">Cảnh báo</div>
                <div className="text-2xl font-bold text-yellow-800">{warningCount}</div>
            </div>
            <div className="bg-orange-50 border border-orange-200 rounded-lg p-3">
                <div className="text-xs text-orange-700 font-medium uppercase">Quá hạn</div>
                <div className="text-2xl font-bold text-orange-800">{overdueCount}</div>
            </div>
            <div className="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                <div className="text-xs text-emerald-700 font-medium uppercase">Lô đang hoạt động</div>
                <div className="text-2xl font-bold text-emerald-800">{activeBatches}</div>
            </div>
        </div>
    );
}

function AlertCard({ alert }) {
    const severityColors = {
        info: 'bg-blue-50 border-blue-200 text-blue-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        critical: 'bg-red-50 border-red-200 text-red-800',
    };
    const colorClass = severityColors[alert.severity] || severityColors.info;

    return (
        <div className={`p-4 rounded-lg border ${colorClass}`}>
            <div className="flex items-start justify-between">
                <div>
                    <p className="font-medium">{alert.title}</p>
                    <p className="text-sm mt-1 opacity-80">{alert.message}</p>
                </div>
                {alert.severity === 'critical' && (
                    <span className="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded">CRITICAL</span>
                )}
            </div>
        </div>
    );
}

function BatchRow({ batch }) {
    return (
        <tr className="border-b border-gray-100 hover:bg-gray-50">
            <td className="px-4 py-3 font-medium">{batchCode(batch)}</td>
            <td className="px-4 py-3 text-gray-600">{batch.crop_name || batch.crop?.name || '-'}</td>
            <td className="px-4 py-3 text-gray-600">{batch.farm?.name || batch.area?.name || '-'}</td>
            <td className="px-4 py-3">
                <span className={`px-2 py-1 text-xs rounded-full ${
                    ACTIVE_BATCH_STATUSES.includes(batch.status) ? 'bg-green-100 text-green-800' :
                    batch.status === 'harvested' ? 'bg-amber-100 text-amber-800' :
                    'bg-gray-100 text-gray-600'
                }`}>
                    {batch.status}
                </span>
            </td>
        </tr>
    );
}

function TaskRow({ task }) {
    const priorityColors = {
        urgent: 'bg-red-600 text-white',
        high: 'bg-orange-500 text-white',
        normal: 'bg-yellow-500 text-black',
        low: 'bg-gray-200 text-gray-700',
    };

    const statusColors = {
        planned: 'bg-gray-100 text-gray-600',
        assigned: 'bg-indigo-100 text-indigo-700',
        in_progress: 'bg-blue-100 text-blue-700',
        done: 'bg-green-100 text-green-700',
        cancelled: 'bg-gray-100 text-gray-400',
    };
    const batch = taskBatch(task);
    const dueDate = taskDueDate(task);

    return (
        <tr className="border-b border-gray-100 hover:bg-gray-50">
            <td className="px-4 py-3 font-medium">{task.title}</td>
            <td className="px-4 py-3 text-gray-600">{batch ? batchCode(batch) : '-'}</td>
            <td className="px-4 py-3">
                <span className={`px-2 py-1 text-xs font-medium rounded ${priorityColors[task.priority] || priorityColors.low}`}>
                    {task.priority}
                </span>
            </td>
            <td className="px-4 py-3 text-gray-600 text-sm">{dueDate || '-'}</td>
            <td className="px-4 py-3">
                <span className={`px-2 py-1 text-xs rounded ${statusColors[task.status] || statusColors.planned}`}>
                    {task.status}
                </span>
            </td>
        </tr>
    );
}

export default function OperationsDashboard() {
    const { logout, user } = useAuth();
    const [activeTab, setActiveTab] = useState('alerts');
    const [search, setSearch] = useState('');
    const [sortBy, setSortBy] = useState('urgent');
    const [data, setData] = useState({ alerts: [], batches: [], tasks: [] });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const loadData = () => {
        setLoading(true);
        setError(null);
        Promise.all([
            api.get('/alerts'),
            api.get('/planting-batches'),
            api.get('/work-tasks'),
        ]).then(([alertsRes, batchesRes, tasksRes]) => {
            setData({
                alerts: alertsRes.data.data || alertsRes.data || [],
                batches: batchesRes.data.data || batchesRes.data || [],
                tasks: tasksRes.data.data || tasksRes.data || [],
            });
        }).catch(err => {
            console.error('Failed to load data:', err);
            setError('Không thể tải dữ liệu. Vui lòng thử lại.');
        }).finally(() => setLoading(false));
    };

    useEffect(() => {
        loadData();
    }, []);

    const filteredAlerts = useMemo(() => {
        let items = [...data.alerts];
        if (search) {
            const q = search.toLowerCase();
            items = items.filter(a =>
                a.title?.toLowerCase().includes(q) ||
                a.message?.toLowerCase().includes(q)
            );
        }
        return items.sort((a, b) => {
            if (sortBy === 'urgent') {
                const pa = SEVERITY_ORDER[a.severity] || 5;
                const pb = SEVERITY_ORDER[b.severity] || 5;
                return pa - pb;
            }
            return 0;
        });
    }, [data.alerts, search, sortBy]);

    const filteredBatches = useMemo(() => {
        let items = [...data.batches];
        if (search) {
            const q = search.toLowerCase();
            items = items.filter(b =>
                b.code?.toLowerCase().includes(q) ||
                b.batch_code?.toLowerCase().includes(q) ||
                b.crop_name?.toLowerCase().includes(q) ||
                b.crop?.name?.toLowerCase().includes(q) ||
                b.farm?.name?.toLowerCase().includes(q)
            );
        }
        return items;
    }, [data.batches, search]);

    const filteredTasks = useMemo(() => {
        let items = [...data.tasks];
        if (search) {
            const q = search.toLowerCase();
            items = items.filter(t =>
                t.title?.toLowerCase().includes(q) ||
                taskBatch(t)?.code?.toLowerCase().includes(q) ||
                taskBatch(t)?.batch_code?.toLowerCase().includes(q)
            );
        }
        if (sortBy === 'status') {
            return items.sort((a, b) => (a.status > b.status ? 1 : -1));
        }
        if (sortBy === 'due') {
            return items.sort((a, b) => {
                const aDueDate = taskDueDate(a);
                const bDueDate = taskDueDate(b);

                if (!aDueDate) return 1;
                if (!bDueDate) return -1;

                return new Date(aDueDate) - new Date(bDueDate);
            });
        }
        return items.sort((a, b) => {
            const pa = PRIORITY_ORDER[a.priority] || 5;
            const pb = PRIORITY_ORDER[b.priority] || 5;
            return pa - pb;
        });
    }, [data.tasks, search, sortBy]);

    const tabs = [
        { id: 'alerts', label: 'Cảnh báo', count: data.alerts.length },
        { id: 'batches', label: 'Lô trồng', count: data.batches.length },
        { id: 'tasks', label: 'Công việc', count: data.tasks.length },
    ];

    const handleLogout = () => {
        logout();
        window.location.href = '/operations/login';
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="text-center">
                    <div className="w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p className="text-gray-600">Đang tải dữ liệu...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-gray-50">
                <div className="text-center max-w-md">
                    <div className="w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                        <svg className="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p className="text-gray-700 font-medium mb-4">{error}</p>
                    <button
                        onClick={loadData}
                        className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition"
                    >
                        Thử lại
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-white shadow-sm border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-800">AgriOps Dashboard</h1>
                        <p className="text-sm text-gray-500">Xin chào, {user?.name || user?.email || 'User'}</p>
                    </div>
                    <button
                        onClick={handleLogout}
                        className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition"
                    >
                        Đăng xuất
                    </button>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 py-6">
                <StatsBar data={data} />

                <div className="flex flex-col sm:flex-row gap-4 mb-6">
                    <div className="flex-1 relative">
                        <input
                            type="text"
                            placeholder="Tìm kiếm..."
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none"
                        />
                        {search && (
                            <button
                                onClick={() => setSearch('')}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            >
                                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        )}
                    </div>
                    <select
                        value={sortBy}
                        onChange={e => setSortBy(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none bg-white"
                    >
                        <option value="urgent">Ưu tiên: Urgent</option>
                        <option value="due">Ngày đến hạn</option>
                        <option value="status">Trạng thái</option>
                    </select>
                </div>

                <div className="flex gap-1 mb-6 bg-gray-100 p-1 rounded-lg overflow-x-auto">
                    {tabs.map(tab => {
                        const counts = {
                            alerts: filteredAlerts.length,
                            batches: filteredBatches.length,
                            tasks: filteredTasks.length,
                        };
                        const totalCounts = {
                            alerts: data.alerts.length,
                            batches: data.batches.length,
                            tasks: data.tasks.length,
                        };
                        const isFiltered = search && counts[tab.id] < totalCounts[tab.id];
                        return (
                            <button
                                key={tab.id}
                                onClick={() => setActiveTab(tab.id)}
                                className={`flex-1 px-4 py-2 text-sm font-medium rounded-md transition whitespace-nowrap ${
                                    activeTab === tab.id
                                        ? 'bg-white text-gray-800 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-800'
                                }`}
                            >
                                {tab.label}
                                <span className="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-200">
                                    {isFiltered ? `${counts[tab.id]}/${totalCounts[tab.id]}` : totalCounts[tab.id]}
                                </span>
                            </button>
                        );
                    })}
                </div>

                {activeTab === 'alerts' && (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {filteredAlerts.length === 0 ? (
                            <p className="text-gray-500 col-span-full text-center py-8">Không có alerts nào</p>
                        ) : (
                            filteredAlerts.map(alert => (
                                <AlertCard key={alert.id} alert={alert} />
                            ))
                        )}
                    </div>
                )}

                {activeTab === 'batches' && (
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[600px]">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Mã lô</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Cây trồng</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Khu vực</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {filteredBatches.length === 0 ? (
                                    <tr>
                                        <td colSpan="4" className="px-4 py-8 text-center text-gray-500">Không có batches nào</td>
                                    </tr>
                                ) : (
                                    filteredBatches.map(batch => (
                                        <BatchRow key={batch.id} batch={batch} />
                                    ))
                                )}
                            </tbody>
                        </table>
                        </div>
                    </div>
                )}

                {activeTab === 'tasks' && (
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[700px]">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Tiêu đề</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Lô</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Ưu tiên</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Đến hạn</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {filteredTasks.length === 0 ? (
                                    <tr>
                                        <td colSpan="5" className="px-4 py-8 text-center text-gray-500">Không có tasks nào</td>
                                    </tr>
                                ) : (
                                    filteredTasks.map(task => (
                                        <TaskRow key={task.id} task={task} />
                                    ))
                                )}
                            </tbody>
                        </table>
                        </div>
                    </div>
                )}
            </main>
        </div>
    );
}
