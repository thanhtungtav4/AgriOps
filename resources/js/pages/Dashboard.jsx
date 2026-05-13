import React, { useState, useEffect, useCallback } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';

const STATUS_BADGES = {
    chua_lam: 'bg-gray-100 text-gray-700',
    dang_lam: 'bg-blue-100 text-blue-700',
    da_bao_cao: 'bg-amber-100 text-amber-700',
    cho_duyet: 'bg-purple-100 text-purple-700',
    hoan_tat: 'bg-green-100 text-green-700',
    tre_han: 'bg-red-100 text-red-700',
    huy: 'bg-gray-100 text-gray-400',
};

const BATCH_STATUS_BADGES = {
    dang_len_ke_hoach: 'bg-gray-100 text-gray-600',
    cho_duyet_ke_hoach: 'bg-amber-100 text-amber-700',
    da_duyet: 'bg-blue-100 text-blue-700',
    dang_lam_dat: 'bg-cyan-100 text-cyan-700',
    dang_gieo_trong: 'bg-teal-100 text-teal-700',
    dang_cham_soc: 'bg-green-100 text-green-700',
    dang_sinh_truong: 'bg-lime-100 text-lime-700',
    dang_ra_hoa: 'bg-yellow-100 text-yellow-700',
    dang_nuoi_trai: 'bg-orange-100 text-orange-700',
    dang_thu_hoach: 'bg-emerald-100 text-emerald-700',
    ket_thuc_thu_hoach: 'bg-green-100 text-green-700',
    dang_cai_tao_dat: 'bg-brown-100 text-brown-700',
    hoan_tat: 'bg-green-100 text-green-700',
    huy: 'bg-gray-100 text-gray-400',
};

const ALERT_TYPE_BADGES = {
    warning: 'bg-amber-100 text-amber-700',
    error: 'bg-red-100 text-red-700',
    info: 'bg-blue-100 text-blue-700',
    success: 'bg-green-100 text-green-700',
};

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}

export default function Dashboard() {
    const { user, logout } = useAuth();
    const [alerts, setAlerts] = useState([]);
    const [batches, setBatches] = useState([]);
    const [tasks, setTasks] = useState([]);
    const [loading, setLoading] = useState(true);
    const [taskSearch, setTaskSearch] = useState('');
    const [taskStatusFilter, setTaskStatusFilter] = useState('all');
    const [taskSort, setTaskSort] = useState('urgent');
    const [batchSearch, setBatchSearch] = useState('');
    const [batchStatusFilter, setBatchStatusFilter] = useState('all');
    const [activeTab, setActiveTab] = useState('tasks');
    const [lastRefresh, setLastRefresh] = useState(new Date());

    const fetchData = useCallback(async () => {
        setLoading(true);
        try {
            const [alertsRes, batchesRes, tasksRes] = await Promise.all([
                api.get('/alerts'),
                api.get('/planting-batches'),
                api.get('/work-tasks'),
            ]);

            setAlerts(alertsRes.data.data || []);
            setBatches(batchesRes.data.data || []);
            setTasks(tasksRes.data.data || []);
            setLastRefresh(new Date());
        } catch (err) {
            console.error('Failed to fetch dashboard data:', err);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchData();
        const interval = setInterval(fetchData, 60000);
        return () => clearInterval(interval);
    }, [fetchData]);

    const markAlertRead = async (id) => {
        try {
            await api.patch(`/alerts/${id}/read`);
            setAlerts(prev => prev.map(a => a.id === id ? { ...a, is_read: true } : a));
        } catch (err) {
            console.error('Failed to mark alert read:', err);
        }
    };

    const filteredTasks = tasks
        .filter(t => {
            if (taskStatusFilter !== 'all' && t.status !== taskStatusFilter) return false;
            if (taskSearch && !t.name?.toLowerCase().includes(taskSearch.toLowerCase())) return false;
            return true;
        })
        .sort((a, b) => {
            if (taskSort === 'urgent') {
                if (a.status === 'tre_han') return -1;
                if (b.status === 'tre_han') return 1;
                if (a.status === 'chua_lam') return -1;
                if (b.status === 'chua_lam') return 1;
            }
            if (taskSort === 'due_date') {
                return new Date(a.due_date || 0) - new Date(b.due_date || 0);
            }
            if (taskSort === 'status') {
                return (a.status || '').localeCompare(b.status || '');
            }
            return 0;
        });

    const filteredBatches = batches
        .filter(b => {
            if (batchStatusFilter !== 'all' && b.status !== batchStatusFilter) return false;
            if (batchSearch && !b.code?.toLowerCase().includes(batchSearch.toLowerCase()) &&
                !b.crop?.name?.toLowerCase().includes(batchSearch.toLowerCase())) return false;
            return true;
        });

    const unreadAlerts = alerts.filter(a => !a.is_read);

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-white shadow-sm border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-semibold text-gray-900">AgriOps Operations</h1>
                        <p className="text-sm text-gray-500">
                            {user?.name} · {user?.role} · {user?.farm?.name || 'Tất cả farms'}
                        </p>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="text-xs text-gray-400">
                            Cập nhật: {formatDateTime(lastRefresh)}
                        </span>
                        <button
                            onClick={fetchData}
                            className="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                        >
                            Làm mới
                        </button>
                        <button
                            onClick={logout}
                            className="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                        >
                            Đăng xuất
                        </button>
                    </div>
                </div>
            </header>

            <main className="max-w-7xl mx-auto px-4 py-6">
                <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <div className="lg:col-span-1 space-y-6">
                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <h2 className="text-sm font-medium text-gray-700 mb-3">
                                Cảnh báo ({unreadAlerts.length})
                            </h2>
                            {loading ? (
                                <div className="text-sm text-gray-400">Đang tải...</div>
                            ) : unreadAlerts.length === 0 ? (
                                <div className="text-sm text-gray-400">Không có cảnh báo mới</div>
                            ) : (
                                <div className="space-y-2">
                                    {unreadAlerts.slice(0, 5).map(alert => (
                                        <div
                                            key={alert.id}
                                            className={`p-3 rounded-lg ${ALERT_TYPE_BADGES[alert.type] || ALERT_TYPE_BADGES.info}`}
                                        >
                                            <p className="text-sm font-medium">{alert.title}</p>
                                            <p className="text-xs mt-1 opacity-75">{alert.message}</p>
                                            <div className="flex justify-between items-center mt-2">
                                                <span className="text-xs opacity-60">{formatDate(alert.created_at)}</span>
                                                <button
                                                    onClick={() => markAlertRead(alert.id)}
                                                    className="text-xs hover:underline"
                                                >
                                                    Đánh dấu đã đọc
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="bg-white rounded-xl shadow-sm p-4">
                            <h2 className="text-sm font-medium text-gray-700 mb-3">Thống kê</h2>
                            <div className="space-y-3">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Lứa trồng</span>
                                    <span className="text-sm font-medium">{batches.length}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Công việc</span>
                                    <span className="text-sm font-medium">{tasks.length}</span>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm text-gray-600">Trễ hạn</span>
                                    <span className="text-sm font-medium text-red-600">
                                        {tasks.filter(t => t.status === 'tre_han').length}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="lg:col-span-3">
                        <div className="bg-white rounded-xl shadow-sm">
                            <div className="border-b border-gray-200">
                                <nav className="flex gap-1 px-4">
                                    <button
                                        onClick={() => setActiveTab('tasks')}
                                        className={`px-4 py-3 text-sm font-medium border-b-2 transition ${
                                            activeTab === 'tasks'
                                                ? 'border-emerald-600 text-emerald-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700'
                                        }`}
                                    >
                                        Công việc ({filteredTasks.length})
                                    </button>
                                    <button
                                        onClick={() => setActiveTab('batches')}
                                        className={`px-4 py-3 text-sm font-medium border-b-2 transition ${
                                            activeTab === 'batches'
                                                ? 'border-emerald-600 text-emerald-600'
                                                : 'border-transparent text-gray-500 hover:text-gray-700'
                                        }`}
                                    >
                                        Lứa trồng ({filteredBatches.length})
                                    </button>
                                </nav>
                            </div>

                            <div className="p-4 border-b border-gray-100">
                                <div className="flex flex-wrap gap-3">
                                    <input
                                        type="text"
                                        placeholder="Tìm kiếm..."
                                        value={activeTab === 'tasks' ? taskSearch : batchSearch}
                                        onChange={(e) => activeTab === 'tasks' ? setTaskSearch(e.target.value) : setBatchSearch(e.target.value)}
                                        className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none"
                                    />
                                    <select
                                        value={activeTab === 'tasks' ? taskStatusFilter : batchStatusFilter}
                                        onChange={(e) => activeTab === 'tasks' ? setTaskStatusFilter(e.target.value) : setBatchStatusFilter(e.target.value)}
                                        className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none"
                                    >
                                        <option value="all">Tất cả trạng thái</option>
                                        {activeTab === 'tasks' ? (
                                            <>
                                                <option value="chua_lam">Chưa làm</option>
                                                <option value="dang_lam">Đang làm</option>
                                                <option value="da_bao_cao">Đã báo cáo</option>
                                                <option value="cho_duyet">Chờ duyệt</option>
                                                <option value="hoan_tat">Hoàn tất</option>
                                                <option value="tre_han">Trễ hạn</option>
                                            </>
                                        ) : (
                                            <>
                                                <option value="dang_len_ke_hoach">Đang lên kế hoạch</option>
                                                <option value="cho_duyet_ke_hoach">Chờ duyệt kế hoạch</option>
                                                <option value="da_duyet">Đã duyệt</option>
                                                <option value="dang_lam_dat">Đang làm đất</option>
                                                <option value="dang_gieo_trong">Đang gieo/trồng</option>
                                                <option value="dang_cham_soc">Đang chăm sóc</option>
                                                <option value="dang_sinh_truong">Đang sinh trưởng</option>
                                                <option value="dang_ra_hoa">Đang ra hoa</option>
                                                <option value="dang_nuoi_trai">Đang nuôi trái</option>
                                                <option value="dang_thu_hoach">Đang thu hoạch</option>
                                                <option value="hoan_tat">Hoàn tất</option>
                                            </>
                                        )}
                                    </select>
                                    <select
                                        value={taskSort}
                                        onChange={(e) => setTaskSort(e.target.value)}
                                        className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none"
                                    >
                                        <option value="urgent">Khẩn cấp</option>
                                        <option value="due_date">Ngày hết hạn</option>
                                        <option value="status">Trạng thái</option>
                                    </select>
                                </div>
                            </div>

                            <div className="p-4">
                                {loading ? (
                                    <div className="text-center py-8 text-gray-400">Đang tải...</div>
                                ) : activeTab === 'tasks' ? (
                                    filteredTasks.length === 0 ? (
                                        <div className="text-center py-8 text-gray-400">Không có công việc phù hợp</div>
                                    ) : (
                                        <div className="space-y-3">
                                            {filteredTasks.map(task => (
                                                <div
                                                    key={task.id}
                                                    className="flex items-center justify-between p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition"
                                                >
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2">
                                                            <h3 className="text-sm font-medium text-gray-900 truncate">
                                                                {task.name}
                                                            </h3>
                                                            <span className={`px-2 py-0.5 text-xs rounded-full ${STATUS_BADGES[task.status] || 'bg-gray-100 text-gray-600'}`}>
                                                                {task.status_label || task.status}
                                                            </span>
                                                        </div>
                                                        <div className="mt-1 flex items-center gap-4 text-xs text-gray-500">
                                                            <span>{task.batch?.code || task.planting_batch_code}</span>
                                                            <span>·</span>
                                                            <span>{task.plot?.name || task.plot_name}</span>
                                                            <span>·</span>
                                                            <span className={task.due_date && new Date(task.due_date) < new Date() && task.status !== 'hoan_tat' ? 'text-red-600' : ''}>
                                                                Hạn: {formatDate(task.due_date)}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )
                                ) : (
                                    filteredBatches.length === 0 ? (
                                        <div className="text-center py-8 text-gray-400">Không có lứa trồng phù hợp</div>
                                    ) : (
                                        <div className="space-y-3">
                                            {filteredBatches.map(batch => (
                                                <div
                                                    key={batch.id}
                                                    className="flex items-center justify-between p-4 border border-gray-100 rounded-lg hover:bg-gray-50 transition"
                                                >
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2">
                                                            <h3 className="text-sm font-medium text-gray-900 truncate">
                                                                {batch.code}
                                                            </h3>
                                                            <span className={`px-2 py-0.5 text-xs rounded-full ${BATCH_STATUS_BADGES[batch.status] || 'bg-gray-100 text-gray-600'}`}>
                                                                {batch.status_label || batch.status}
                                                            </span>
                                                        </div>
                                                        <div className="mt-1 flex items-center gap-4 text-xs text-gray-500">
                                                            <span>{batch.crop?.name || batch.crop_name}</span>
                                                            <span>·</span>
                                                            <span>{batch.variety?.name || batch.variety_name}</span>
                                                            <span>·</span>
                                                            <span>Ngày trồng: {formatDate(batch.actual_planting_date || batch.planned_planting_date)}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}