import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../../services/api';

const STATUS_LABELS = {
  planned: 'Chưa giao',
  assigned: 'Đã giao',
  in_progress: 'Đang làm',
  done: 'Hoàn tất',
  cancelled: 'Đã hủy',
};

const STATUS_CLASSES = {
  planned: 'bg-gray-100 text-gray-700',
  assigned: 'bg-blue-100 text-blue-700',
  in_progress: 'bg-yellow-100 text-yellow-800',
  done: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700',
};

const PRIORITY_CLASSES = {
  low: 'bg-gray-100 text-gray-700',
  normal: 'bg-sky-100 text-sky-700',
  high: 'bg-orange-100 text-orange-700',
  urgent: 'bg-red-100 text-red-700',
};

const formatDate = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('vi-VN');
};

const batchCode = (task) => task.planting_batch?.code || task.plantingBatch?.code || task.planting_batch?.id || '-';

const TaskStatusBadge = ({ status }) => (
  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${STATUS_CLASSES[status] || STATUS_CLASSES.planned}`}>
    {STATUS_LABELS[status] || status}
  </span>
);

const PriorityBadge = ({ priority }) => (
  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${PRIORITY_CLASSES[priority] || PRIORITY_CLASSES.normal}`}>
    {priority || 'normal'}
  </span>
);

const TasksPage = () => {
  const navigate = useNavigate();
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [filters, setFilters] = useState({
    search: '',
    status: '',
    due: '',
  });

  const loadTasks = async () => {
    setLoading(true);
    setError(null);

    try {
      const params = {};
      if (filters.status) params.status = filters.status;
      if (filters.due === 'overdue' || filters.due === 'today') {
        params.due_before = new Date().toISOString().slice(0, 10);
      }

      const response = await api.get('/work-tasks', { params });
      setTasks(response.data.data || response.data || []);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải danh sách công việc.');
      console.error('Error loading work tasks:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadTasks();
  }, [filters.status, filters.due]);

  const filteredTasks = useMemo(() => {
    let items = [...tasks];
    const query = filters.search.trim().toLowerCase();

    if (query) {
      items = items.filter((task) => {
        const cropName = task.planting_batch?.crop?.name || task.plantingBatch?.crop?.name || '';
        const code = String(batchCode(task));
        return (
          task.title?.toLowerCase().includes(query) ||
          task.task_type?.toLowerCase().includes(query) ||
          cropName.toLowerCase().includes(query) ||
          code.toLowerCase().includes(query)
        );
      });
    }

    if (filters.due === 'overdue') {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      items = items.filter((task) => task.planned_due_date && new Date(task.planned_due_date) < today && !['done', 'cancelled'].includes(task.status));
    }

    if (filters.due === 'today') {
      const today = new Date().toISOString().slice(0, 10);
      items = items.filter((task) => task.planned_due_date?.slice(0, 10) === today);
    }

    return items.sort((a, b) => {
      if (!a.planned_due_date) return 1;
      if (!b.planned_due_date) return -1;
      return new Date(a.planned_due_date) - new Date(b.planned_due_date);
    });
  }, [tasks, filters.search, filters.due]);

  const counts = useMemo(() => ({
    all: tasks.length,
    planned: tasks.filter((task) => task.status === 'planned').length,
    assigned: tasks.filter((task) => task.status === 'assigned').length,
    in_progress: tasks.filter((task) => task.status === 'in_progress').length,
    done: tasks.filter((task) => task.status === 'done').length,
  }), [tasks]);

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-gray-800">Công việc canh tác</h1>
        <p className="text-gray-600">Theo dõi, giao việc và ghi nhận thực tế sản xuất theo từng lứa trồng.</p>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        {[
          ['all', 'Tất cả'],
          ['planned', 'Chưa giao'],
          ['assigned', 'Đã giao'],
          ['in_progress', 'Đang làm'],
          ['done', 'Hoàn tất'],
        ].map(([key, label]) => (
          <button
            key={key}
            type="button"
            onClick={() => setFilters((prev) => ({ ...prev, status: key === 'all' ? '' : key }))}
            className={`text-left bg-white border rounded-lg p-3 hover:border-emerald-300 ${
              (key === 'all' && !filters.status) || filters.status === key ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-200'
            }`}
          >
            <div className="text-xs text-gray-500">{label}</div>
            <div className="text-2xl font-semibold text-gray-800">{counts[key] ?? 0}</div>
          </button>
        ))}
      </div>

      <div className="bg-white rounded-lg shadow border border-gray-200">
        <div className="p-4 border-b border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-3">
          <input
            type="text"
            value={filters.search}
            onChange={(event) => setFilters((prev) => ({ ...prev, search: event.target.value }))}
            placeholder="Tìm theo tên việc, lứa, cây trồng..."
            className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
          />
          <select
            value={filters.status}
            onChange={(event) => setFilters((prev) => ({ ...prev, status: event.target.value }))}
            className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
          >
            <option value="">Mọi trạng thái</option>
            {Object.entries(STATUS_LABELS).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
          <select
            value={filters.due}
            onChange={(event) => setFilters((prev) => ({ ...prev, due: event.target.value }))}
            className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
          >
            <option value="">Mọi hạn việc</option>
            <option value="today">Đến hạn hôm nay</option>
            <option value="overdue">Đang quá hạn</option>
          </select>
        </div>

        {error && (
          <div className="m-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>
        )}

        {loading ? (
          <div className="p-8 text-center text-gray-600">Đang tải công việc...</div>
        ) : filteredTasks.length === 0 ? (
          <div className="p-8 text-center">
            <h3 className="text-lg font-medium text-gray-900 mb-1">Chưa có công việc phù hợp</h3>
            <p className="text-gray-500">Có thể tạo công việc từ trang chi tiết lứa trồng.</p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Công việc</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lứa</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ưu tiên</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hạn</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người phụ trách</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {filteredTasks.map((task) => (
                  <tr key={task.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <div className="font-medium text-gray-900">{task.title}</div>
                      <div className="text-xs text-gray-500">{task.task_type || '-'}</div>
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-700">{batchCode(task)}</td>
                    <td className="px-4 py-3"><PriorityBadge priority={task.priority} /></td>
                    <td className="px-4 py-3 text-sm text-gray-700">{formatDate(task.planned_due_date)}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{task.assigned_user?.name || 'Chưa giao'}</td>
                    <td className="px-4 py-3"><TaskStatusBadge status={task.status} /></td>
                    <td className="px-4 py-3">
                      <button
                        type="button"
                        onClick={() => navigate(`/operations/tasks/${task.id}`)}
                        className="text-emerald-700 hover:text-emerald-900 font-medium text-sm"
                      >
                        Xử lý
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

export default TasksPage;

