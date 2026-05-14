import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
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

const TRANSITIONS = {
  planned: ['assigned', 'cancelled'],
  assigned: ['in_progress', 'cancelled'],
  in_progress: ['done', 'cancelled'],
  done: [],
  cancelled: [],
};

const formatDateTime = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleString('vi-VN');
};

const StatusBadge = ({ status }) => (
  <span className={`inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ${STATUS_CLASSES[status] || STATUS_CLASSES.planned}`}>
    {STATUS_LABELS[status] || status}
  </span>
);

const Field = ({ label, value }) => (
  <div>
    <div className="text-xs font-medium text-gray-500 uppercase tracking-wide">{label}</div>
    <div className="mt-1 text-sm text-gray-900">{value || '-'}</div>
  </div>
);

const TaskDetailPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [task, setTask] = useState(null);
  const [submittedLogs, setSubmittedLogs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [savingStatus, setSavingStatus] = useState(false);
  const [savingLog, setSavingLog] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  const [transitionData, setTransitionData] = useState({
    status: '',
    assigned_user_id: '',
    reason: '',
    completion_note: '',
  });

  const [logData, setLogData] = useState({
    actual_start_at: '',
    actual_end_at: '',
    notes: '',
    photo_paths: '',
  });
  const [logPhotos, setLogPhotos] = useState([]);

  const loadTask = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await api.get(`/work-tasks/${id}`);
      setTask(response.data.data);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải chi tiết công việc.');
      console.error('Error loading work task:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadTask();
  }, [id]);

  const allowedTransitions = useMemo(() => TRANSITIONS[task?.status] || [], [task?.status]);

  const handleStatusSubmit = async (event) => {
    event.preventDefault();
    if (!transitionData.status) return;

    setSavingStatus(true);
    setError(null);
    setSuccess(null);

    try {
      const payload = { status: transitionData.status };

      if (transitionData.status === 'assigned') {
        payload.assigned_user_id = Number(transitionData.assigned_user_id);
      }

      if (transitionData.status === 'cancelled') {
        payload.reason = transitionData.reason.trim();
      }

      if (transitionData.status === 'done') {
        payload.completion_note = transitionData.completion_note.trim();
      }

      const response = await api.patch(`/work-tasks/${id}/status`, payload);
      setTask(response.data.data);
      setTransitionData({ status: '', assigned_user_id: '', reason: '', completion_note: '' });
      setSuccess('Đã cập nhật trạng thái công việc.');
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể cập nhật trạng thái công việc.');
      console.error('Error updating task status:', err);
    } finally {
      setSavingStatus(false);
    }
  };

  const handleLogSubmit = async (event) => {
    event.preventDefault();
    setSavingLog(true);
    setError(null);
    setSuccess(null);

    try {
      const photoPaths = logData.photo_paths
        .split('\n')
        .map((item) => item.trim())
        .filter(Boolean);

      let response;

      if (logPhotos.length > 0) {
        const payload = new FormData();
        if (logData.notes) payload.append('notes', logData.notes);
        if (logData.actual_start_at) payload.append('actual_start_at', logData.actual_start_at);
        if (logData.actual_end_at) payload.append('actual_end_at', logData.actual_end_at);
        photoPaths.forEach((path) => payload.append('photo_paths[]', path));
        logPhotos.forEach((photo) => payload.append('photos[]', photo));

        response = await api.post(`/work-tasks/${id}/logs`, payload, {
          headers: { 'Content-Type': 'multipart/form-data' },
        });
      } else {
        const payload = {
          notes: logData.notes,
          actual_start_at: logData.actual_start_at || undefined,
          actual_end_at: logData.actual_end_at || undefined,
          photo_paths: photoPaths.length ? photoPaths : undefined,
        };

        Object.keys(payload).forEach((key) => {
          if (payload[key] === undefined || payload[key] === '') delete payload[key];
        });

        response = await api.post(`/work-tasks/${id}/logs`, payload);
      }

      setSubmittedLogs((prev) => [response.data.data, ...prev]);
      setLogData({ actual_start_at: '', actual_end_at: '', notes: '', photo_paths: '' });
      setLogPhotos([]);
      setSuccess('Đã ghi nhận nhật ký thực tế và hoàn tất công việc.');
      await loadTask();
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể ghi nhật ký công việc.');
      console.error('Error submitting task log:', err);
    } finally {
      setSavingLog(false);
    }
  };

  if (loading) {
    return (
      <div className="max-w-5xl mx-auto">
        <button onClick={() => navigate('/operations/tasks')} className="text-emerald-700 hover:text-emerald-900 mb-4">
          Quay lại danh sách
        </button>
        <div className="bg-white rounded-lg shadow border border-gray-200 p-8 text-center text-gray-600">Đang tải công việc...</div>
      </div>
    );
  }

  if (!task) {
    return (
      <div className="max-w-5xl mx-auto">
        <button onClick={() => navigate('/operations/tasks')} className="text-emerald-700 hover:text-emerald-900 mb-4">
          Quay lại danh sách
        </button>
        <div className="bg-white rounded-lg shadow border border-gray-200 p-8 text-center text-gray-600">Không tìm thấy công việc.</div>
      </div>
    );
  }

  return (
    <div className="max-w-5xl mx-auto">
      <button
        type="button"
        onClick={() => navigate('/operations/tasks')}
        className="text-emerald-700 hover:text-emerald-900 mb-4"
      >
        Quay lại danh sách
      </button>

      <div className="mb-6 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-gray-800">{task.title}</h1>
          <p className="text-gray-600">Công việc #{task.id} trong lứa {task.planting_batch?.code || task.plantingBatch?.code || task.planting_batch_id || '-'}</p>
        </div>
        <StatusBadge status={task.status} />
      </div>

      {error && (
        <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>
      )}
      {success && (
        <div className="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800">{success}</div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Thông tin công việc</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Field label="Loại việc" value={task.task_type} />
              <Field label="Ưu tiên" value={task.priority} />
              <Field label="Ngày bắt đầu kế hoạch" value={formatDateTime(task.planned_start_date)} />
              <Field label="Hạn hoàn thành" value={formatDateTime(task.planned_due_date)} />
              <Field label="Người phụ trách" value={task.assigned_user?.name || task.assigned_user_id} />
              <Field label="Giai đoạn" value={task.growth_stage?.name} />
              <Field label="Lô đất" value={task.plot?.name || task.plot?.code} />
              <Field label="Luống" value={task.bed?.name || task.bed?.code} />
              <div className="md:col-span-2">
                <Field label="Hướng dẫn" value={task.instructions || 'Chưa có hướng dẫn chi tiết.'} />
              </div>
              <div className="md:col-span-2">
                <Field label="Ghi chú hoàn tất" value={task.completion_note} />
              </div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Ghi nhận nhật ký thực tế</h2>
            {['done', 'cancelled'].includes(task.status) ? (
              <p className="text-sm text-gray-600">Công việc đã ở trạng thái cuối nên không thể gửi thêm nhật ký mới.</p>
            ) : (
              <form onSubmit={handleLogSubmit} className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Bắt đầu thực tế</label>
                    <input
                      type="datetime-local"
                      value={logData.actual_start_at}
                      onChange={(event) => setLogData((prev) => ({ ...prev, actual_start_at: event.target.value }))}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Kết thúc thực tế</label>
                    <input
                      type="datetime-local"
                      value={logData.actual_end_at}
                      onChange={(event) => setLogData((prev) => ({ ...prev, actual_end_at: event.target.value }))}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Ghi chú thực tế</label>
                  <textarea
                    value={logData.notes}
                    onChange={(event) => setLogData((prev) => ({ ...prev, notes: event.target.value }))}
                    rows="4"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    placeholder="Ghi nhận tình trạng cây, sản lượng, vấn đề phát sinh..."
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Upload ảnh minh chứng</label>
                  <input
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={(event) => setLogPhotos(Array.from(event.target.files || []))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  />
                  {logPhotos.length > 0 && (
                    <p className="text-xs text-gray-500 mt-1">{logPhotos.length} ảnh đã chọn</p>
                  )}
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Đường dẫn ảnh đã có</label>
                  <textarea
                    value={logData.photo_paths}
                    onChange={(event) => setLogData((prev) => ({ ...prev, photo_paths: event.target.value }))}
                    rows="3"
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    placeholder="Mỗi dòng một đường dẫn ảnh đã upload"
                  />
                </div>
                <button
                  type="submit"
                  disabled={savingLog}
                  className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                >
                  {savingLog ? 'Đang lưu...' : 'Lưu nhật ký và hoàn tất'}
                </button>
              </form>
            )}

            {submittedLogs.length > 0 && (
              <div className="mt-6 border-t border-gray-200 pt-4">
                <h3 className="text-sm font-medium text-gray-700 mb-3">Nhật ký vừa gửi</h3>
                <div className="space-y-3">
                  {submittedLogs.map((log) => (
                    <div key={log.id} className="p-3 bg-gray-50 rounded-lg border border-gray-200">
                      <div className="text-xs text-gray-500">{formatDateTime(log.logged_at)}</div>
                      <div className="text-sm text-gray-800 mt-1">{log.notes || 'Không có ghi chú'}</div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Chuyển trạng thái</h2>
            {allowedTransitions.length === 0 ? (
              <p className="text-sm text-gray-600">Công việc đã hoàn tất hoặc đã hủy.</p>
            ) : (
              <form onSubmit={handleStatusSubmit} className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Trạng thái tiếp theo</label>
                  <select
                    value={transitionData.status}
                    onChange={(event) => setTransitionData((prev) => ({ ...prev, status: event.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white"
                    required
                  >
                    <option value="">Chọn trạng thái</option>
                    {allowedTransitions.map((status) => (
                      <option key={status} value={status}>{STATUS_LABELS[status] || status}</option>
                    ))}
                  </select>
                </div>

                {transitionData.status === 'assigned' && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">ID nhân công</label>
                    <input
                      type="number"
                      min="1"
                      value={transitionData.assigned_user_id}
                      onChange={(event) => setTransitionData((prev) => ({ ...prev, assigned_user_id: event.target.value }))}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                      required
                    />
                  </div>
                )}

                {transitionData.status === 'cancelled' && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Lý do hủy</label>
                    <textarea
                      value={transitionData.reason}
                      onChange={(event) => setTransitionData((prev) => ({ ...prev, reason: event.target.value }))}
                      rows="3"
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                      required
                    />
                  </div>
                )}

                {transitionData.status === 'done' && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Ghi chú hoàn tất</label>
                    <textarea
                      value={transitionData.completion_note}
                      onChange={(event) => setTransitionData((prev) => ({ ...prev, completion_note: event.target.value }))}
                      rows="3"
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                  </div>
                )}

                <button
                  type="submit"
                  disabled={savingStatus}
                  className="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                >
                  {savingStatus ? 'Đang cập nhật...' : 'Cập nhật trạng thái'}
                </button>
              </form>
            )}
          </div>

          <div className="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Liên kết nhanh</h2>
            <div className="space-y-2">
              {task.planting_batch_id && (
                <button
                  type="button"
                  onClick={() => navigate(`/operations/batches/${task.planting_batch_id}`)}
                  className="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm"
                >
                  Xem lứa trồng
                </button>
              )}
              <button
                type="button"
                onClick={loadTask}
                className="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm"
              >
                Tải lại dữ liệu
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TaskDetailPage;
