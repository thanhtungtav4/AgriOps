import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';

const BatchDetailPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [batch, setBatch] = useState(null);
  const [plots, setPlots] = useState([]);
  const [selectedPlotBeds, setSelectedPlotBeds] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [actionError, setActionError] = useState(null);
  const [transitionLoading, setTransitionLoading] = useState(false);
  const [generateLoading, setGenerateLoading] = useState(false);
  const [allocationLoading, setAllocationLoading] = useState(false);
  const [tasks, setTasks] = useState([]);
  const [showTransitionModal, setShowTransitionModal] = useState(false);
  const [transitionData, setTransitionData] = useState({ to_status: '', reason: '' });
  const [allocationForm, setAllocationForm] = useState({
    plot_id: '',
    bed_id: '',
    allocated_area_m2: '',
    notes: '',
  });

  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Fetch batch details
      const batchResponse = await api.get(`/planting-batches/${id}`);
      const nextBatch = batchResponse.data.data;
      setBatch(nextBatch);
      
      // Fetch related work tasks and farm plots
      const [tasksResponse, plotsResponse] = await Promise.all([
        api.get(`/work-tasks?planting_batch_id=${id}`),
        api.get('/plots', { params: { farm_id: nextBatch.farm_id } }),
      ]);
      setTasks(tasksResponse.data.data || tasksResponse.data || []);
      setPlots(plotsResponse.data.data || plotsResponse.data || []);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải chi tiết lứa trồng. Vui lòng thử lại.');
      console.error('Error fetching batch details:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, [id]);

  const getStatusBadge = (status) => {
    switch (status) {
      case 'planned':
      case 'planned_kh':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Đã lập kế hoạch</span>;
      case 'approved':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Đã duyệt</span>;
      case 'soil_prep':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Chuẩn bị đất</span>;
      case 'planting':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Đang trồng</span>;
      case 'growing':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">Đang chăm sóc</span>;
      case 'flowering':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800">Ra hoa</span>;
      case 'fruiting':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Nuôi trái</span>;
      case 'harvesting':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Đang thu hoạch</span>;
      case 'completed':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Hoàn tất</span>;
      case 'cancelled':
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Đã hủy</span>;
      default:
        return <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{status}</span>;
    }
  };

  const getAllowedTransitions = () => {
    if (!batch) return [];
    
    const transitions = {
      'planned': ['approved', 'cancelled'],
      'planned_kh': ['approved', 'cancelled'],
      'approved': ['soil_prep', 'cancelled'],
      'soil_prep': ['planting', 'cancelled'],
      'planting': ['growing', 'cancelled'],
      'growing': ['flowering', 'fruiting', 'cancelled'],
      'flowering': ['fruiting', 'cancelled'],
      'fruiting': ['harvesting', 'cancelled'],
      'harvesting': ['completed', 'cancelled'],
      'completed': [],
      'cancelled': []
    };
    
    return transitions[batch.status] || [];
  };

  const handleTransition = async () => {
    if (!transitionData.to_status) return;
    if (transitionData.to_status === 'cancelled' && transitionData.reason.trim().length < 10) {
      setError('Lý do hủy cần tối thiểu 10 ký tự.');
      return;
    }
    
    setTransitionLoading(true);
    try {
      const payload = { to_status: transitionData.to_status };
      if (transitionData.to_status === 'cancelled' && transitionData.reason) {
        payload.reason = transitionData.reason.trim();
      }
      
      await api.patch(`/planting-batches/${id}/transition`, payload);
      await fetchData();
      setShowTransitionModal(false);
      setTransitionData({ to_status: '', reason: '' });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể chuyển trạng thái. Vui lòng thử lại.');
      console.error('Error transitioning batch:', err);
    } finally {
      setTransitionLoading(false);
    }
  };

  const handleGenerateTasks = async (allocationId = null) => {
    setGenerateLoading(true);
    setActionError(null);
    try {
      const payload = allocationId ? { allocation_id: allocationId } : {};
      await api.post(`/planting-batches/${id}/generate-work-tasks`, payload);
      // Refresh tasks
      const tasksResponse = await api.get(`/work-tasks?planting_batch_id=${id}`);
      setTasks(tasksResponse.data.data || tasksResponse.data || []);
    } catch (err) {
      setActionError(err.response?.data?.error?.message || 'Không thể tạo công việc. Vui lòng thử lại.');
      console.error('Error generating tasks:', err);
    } finally {
      setGenerateLoading(false);
    }
  };

  const handlePlotChange = async (plotId) => {
    setAllocationForm(prev => ({ ...prev, plot_id: plotId, bed_id: '' }));
    setSelectedPlotBeds([]);

    if (!plotId) return;

    try {
      const response = await api.get(`/plots/${plotId}`);
      setSelectedPlotBeds(response.data.data?.beds || []);
    } catch (err) {
      setActionError(err.response?.data?.error?.message || 'Không thể tải danh sách luống.');
      console.error('Error loading plot beds:', err);
    }
  };

  const handleCreateAllocation = async (event) => {
    event.preventDefault();
    setAllocationLoading(true);
    setActionError(null);

    try {
      const payload = {
        plot_id: allocationForm.plot_id,
        bed_id: allocationForm.bed_id || undefined,
        allocated_area_m2: allocationForm.allocated_area_m2 ? parseFloat(allocationForm.allocated_area_m2) : undefined,
        notes: allocationForm.notes || undefined,
      };

      Object.keys(payload).forEach((key) => {
        if (payload[key] === undefined || payload[key] === '') delete payload[key];
      });

      await api.post(`/planting-batches/${id}/allocations`, payload);
      setAllocationForm({ plot_id: '', bed_id: '', allocated_area_m2: '', notes: '' });
      setSelectedPlotBeds([]);
      await fetchData();
    } catch (err) {
      setActionError(err.response?.data?.error?.message || 'Không thể phân bổ đất cho lứa trồng.');
      console.error('Error creating allocation:', err);
    } finally {
      setAllocationLoading(false);
    }
  };

  const handleRemoveAllocation = async (allocationId) => {
    if (!window.confirm('Bỏ phân bổ đất này?')) return;

    setAllocationLoading(true);
    setActionError(null);

    try {
      await api.delete(`/planting-batches/${id}/allocations/${allocationId}`, {
        data: { reason: 'Removed from operations UI' },
      });
      await fetchData();
    } catch (err) {
      setActionError(err.response?.data?.error?.message || 'Không thể bỏ phân bổ đất.');
      console.error('Error removing allocation:', err);
    } finally {
      setAllocationLoading(false);
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
  };

  if (loading) {
    return (
      <div className="max-w-6xl mx-auto">
        <div className="mb-6">
          <button 
            onClick={() => navigate(-1)}
            className="flex items-center text-emerald-600 hover:text-emerald-800 mb-4"
          >
            <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Quay lại
          </button>
          
          <div className="flex justify-between items-center">
            <h1 className="text-2xl font-semibold text-gray-800">Chi tiết lứa trồng</h1>
          </div>
        </div>
        
        <div className="bg-white rounded-lg shadow">
          <div className="p-6">
            <div className="animate-pulse flex items-center justify-center h-32">
              <div className="text-center">
                <div className="w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p className="text-gray-600">Đang tải chi tiết lứa trồng...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="max-w-6xl mx-auto">
        <div className="mb-6">
          <button 
            onClick={() => navigate(-1)}
            className="flex items-center text-emerald-600 hover:text-emerald-800 mb-4"
          >
            <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Quay lại
          </button>
          
          <div className="flex justify-between items-center">
            <h1 className="text-2xl font-semibold text-gray-800">Chi tiết lứa trồng</h1>
          </div>
        </div>
        
        <div className="bg-white rounded-lg shadow">
          <div className="p-6">
            <div className="text-center">
              <div className="text-red-600 mb-2">❌</div>
              <p className="text-red-600">{error}</p>
              <button 
                onClick={() => window.location.reload()}
                className="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
              >
                Thử lại
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (!batch) {
    return (
      <div className="max-w-6xl mx-auto">
        <div className="mb-6">
          <button 
            onClick={() => navigate(-1)}
            className="flex items-center text-emerald-600 hover:text-emerald-800 mb-4"
          >
            <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Quay lại
          </button>
          
          <div className="flex justify-between items-center">
            <h1 className="text-2xl font-semibold text-gray-800">Chi tiết lứa trồng</h1>
          </div>
        </div>
        
        <div className="bg-white rounded-lg shadow">
          <div className="p-6 text-center">
            <p className="text-gray-600">Không tìm thấy lứa trồng</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-6">
        <button 
          onClick={() => navigate(-1)}
          className="flex items-center text-emerald-600 hover:text-emerald-800 mb-4"
        >
          <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          Quay lại
        </button>
        
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-2xl font-semibold text-gray-800">Chi tiết lứa trồng #{batch.id}</h1>
            <p className="text-gray-600">Mã: {batch.code || 'N/A'} • {batch.crop?.name || 'N/A'}</p>
          </div>
          <div className="flex space-x-3">
            <button
              onClick={handleGenerateTasks}
              disabled={generateLoading}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {generateLoading ? 'Đang tạo...' : 'Tạo công việc'}
            </button>
            
            <button
              onClick={() => setShowTransitionModal(true)}
              className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
            >
              Chuyển trạng thái
            </button>
          </div>
        </div>
      </div>
      
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Batch Info */}
        <div className="lg:col-span-2 space-y-6">
          {actionError && (
            <div className="p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">
              {actionError}
            </div>
          )}

          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Thông tin lứa trồng</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                <div>{getStatusBadge(batch.status)}</div>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Cây trồng</label>
                <p className="text-sm text-gray-900">{batch.crop?.name || 'N/A'}</p>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Farm</label>
                <p className="text-sm text-gray-900">{batch.farm?.name || 'N/A'}</p>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">SL dự kiến</label>
                <p className="text-sm text-gray-900">{batch.planned_quantity?.toLocaleString('vi-VN')} {batch.planned_unit || ''}</p>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Diện tích</label>
                <p className="text-sm text-gray-900">{batch.planned_area_m2 ? `${batch.planned_area_m2} m²` : 'N/A'}</p>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
                <p className="text-sm text-gray-900">{formatDate(batch.planned_start_date)}</p>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Ngày thu hoạch</label>
                <p className="text-sm text-gray-900">{formatDate(batch.planned_harvest_date)}</p>
              </div>
              
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                <p className="text-sm text-gray-900">{batch.notes || 'Không có ghi chú'}</p>
              </div>
            </div>
          </div>

          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
              <div>
                <h2 className="text-lg font-medium text-gray-800">Phân bổ đất và luống</h2>
                <p className="text-sm text-gray-500">Gắn lứa trồng với lô đất/luống thực tế để sinh công việc theo khu vực.</p>
              </div>
              <span className="text-sm text-gray-500">{batch.allocations?.length || 0} phân bổ</span>
            </div>

            <form onSubmit={handleCreateAllocation} className="grid grid-cols-1 md:grid-cols-5 gap-3 mb-5">
              <div className="md:col-span-2">
                <label className="block text-sm font-medium text-gray-700 mb-1">Lô đất</label>
                <select
                  value={allocationForm.plot_id}
                  onChange={(event) => handlePlotChange(event.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
                  required
                >
                  <option value="">Chọn lô đất</option>
                  {plots.map((plot) => (
                    <option key={plot.id} value={plot.id}>
                      {plot.name || plot.code} - {plot.status} - {plot.area_m2 || 0} m²
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Luống</label>
                <select
                  value={allocationForm.bed_id}
                  onChange={(event) => setAllocationForm(prev => ({ ...prev, bed_id: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
                  disabled={!selectedPlotBeds.length}
                >
                  <option value="">Toàn lô</option>
                  {selectedPlotBeds.map((bed) => (
                    <option key={bed.id} value={bed.id}>
                      {bed.name || bed.code}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Diện tích m²</label>
                <input
                  type="number"
                  min="0.01"
                  step="any"
                  value={allocationForm.allocated_area_m2}
                  onChange={(event) => setAllocationForm(prev => ({ ...prev, allocated_area_m2: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                  placeholder="Tự lấy diện tích lô"
                />
              </div>
              <div className="flex items-end">
                <button
                  type="submit"
                  disabled={allocationLoading}
                  className="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50"
                >
                  {allocationLoading ? 'Đang lưu...' : 'Phân bổ'}
                </button>
              </div>
              <div className="md:col-span-5">
                <textarea
                  value={allocationForm.notes}
                  onChange={(event) => setAllocationForm(prev => ({ ...prev, notes: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                  rows="2"
                  placeholder="Ghi chú phân bổ..."
                />
              </div>
            </form>

            {!batch.allocations?.length ? (
              <div className="text-center py-6 bg-gray-50 rounded-lg text-gray-500">Chưa phân bổ lô đất/luống cho lứa này.</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lô đất</th>
                      <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Luống</th>
                      <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Diện tích</th>
                      <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                      <th className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200">
                    {batch.allocations.map((allocation) => (
                      <tr key={allocation.id}>
                        <td className="px-3 py-2 text-sm text-gray-900">{allocation.plot?.name || allocation.plot?.code || allocation.plot_id}</td>
                        <td className="px-3 py-2 text-sm text-gray-700">{allocation.bed?.name || allocation.bed?.code || allocation.bed_id || 'Toàn lô'}</td>
                        <td className="px-3 py-2 text-sm text-gray-700">{allocation.allocated_area_m2 || '-'} m²</td>
                        <td className="px-3 py-2 text-sm text-gray-700">{allocation.status}</td>
                        <td className="px-3 py-2">
                          <div className="flex gap-3">
                            <button
                              type="button"
                              onClick={() => handleGenerateTasks(allocation.id)}
                              disabled={generateLoading}
                              className="text-blue-700 hover:text-blue-900 text-sm font-medium"
                            >
                              Tạo việc
                            </button>
                            <button
                              type="button"
                              onClick={() => handleRemoveAllocation(allocation.id)}
                              disabled={allocationLoading}
                              className="text-red-700 hover:text-red-900 text-sm font-medium"
                            >
                              Bỏ
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
          
          {/* Work Tasks */}
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-medium text-gray-800">Công việc thực hiện</h2>
              <span className="text-sm text-gray-500">{tasks.length} công việc</span>
            </div>
            
            {tasks.length === 0 ? (
              <div className="text-center py-8">
                <div className="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                  <svg className="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <p className="text-gray-500">Chưa có công việc nào được tạo</p>
                <button
                  onClick={handleGenerateTasks}
                  disabled={generateLoading}
                  className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {generateLoading ? 'Đang tạo...' : 'Tạo công việc từ lứa trồng'}
                </button>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th scope="col" className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tên công việc
                      </th>
                      <th scope="col" className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Trạng thái
                      </th>
                      <th scope="col" className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ngày đến hạn
                      </th>
                      <th scope="col" className="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Người phụ trách
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {tasks.map((task) => (
                      <tr key={task.id} className="hover:bg-gray-50">
                        <td className="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                          {task.title}
                        </td>
                        <td className="px-3 py-2 whitespace-nowrap">
                          <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${
                            task.status === 'planned' ? 'bg-gray-100 text-gray-800' :
                            task.status === 'assigned' ? 'bg-blue-100 text-blue-800' :
                            task.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                            task.status === 'done' ? 'bg-green-100 text-green-800' :
                            task.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                            'bg-gray-100 text-gray-800'
                          }`}>
                            {task.status === 'planned' && 'Chưa giao'}
                            {task.status === 'assigned' && 'Đã giao'}
                            {task.status === 'in_progress' && 'Đang thực hiện'}
                            {task.status === 'done' && 'Hoàn tất'}
                            {task.status === 'cancelled' && 'Đã hủy'}
                          </span>
                        </td>
                        <td className="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                          {formatDate(task.planned_due_date)}
                        </td>
                        <td className="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                          {task.assigned_user?.name || 'Chưa phân công'}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
        
        {/* Right sidebar - Actions and timeline */}
        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Hành động nhanh</h2>
            
            <div className="space-y-3">
              <button
                onClick={handleGenerateTasks}
                disabled={generateLoading}
                className="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
              >
                {generateLoading ? 'Đang tạo...' : 'Tạo công việc'}
              </button>
              
              <button
                onClick={() => setShowTransitionModal(true)}
                className="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 text-sm"
              >
                Chuyển trạng thái
              </button>
            </div>
          </div>
          
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Thông tin thêm</h2>
            
            <div className="space-y-3 text-sm">
              <div>
                <label className="text-xs font-medium text-gray-500 uppercase tracking-wider">Tạo lúc</label>
                <p className="text-gray-900">{formatDate(batch.created_at)}</p>
              </div>
              
              <div>
                <label className="text-xs font-medium text-gray-500 uppercase tracking-wider">Cập nhật</label>
                <p className="text-gray-900">{formatDate(batch.updated_at)}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {/* Transition Modal */}
      {showTransitionModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div className="p-6">
              <h3 className="text-lg font-medium text-gray-800 mb-4">Chuyển trạng thái lứa trồng</h3>
              
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">Trạng thái hiện tại</label>
                <div>{getStatusBadge(batch.status)}</div>
              </div>
              
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">Chuyển sang</label>
                <select
                  value={transitionData.to_status}
                  onChange={(e) => setTransitionData({...transitionData, to_status: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                >
                  <option value="">Chọn trạng thái</option>
                  {getAllowedTransitions().map(status => (
                    <option key={status} value={status}>
                      {status === 'planned' && 'Đã lập kế hoạch'}
                      {status === 'approved' && 'Đã duyệt'}
                      {status === 'soil_prep' && 'Chuẩn bị đất'}
                      {status === 'planting' && 'Đang trồng'}
                      {status === 'growing' && 'Đang chăm sóc'}
                      {status === 'flowering' && 'Ra hoa'}
                      {status === 'fruiting' && 'Nuôi trái'}
                      {status === 'harvesting' && 'Đang thu hoạch'}
                      {status === 'completed' && 'Hoàn tất'}
                      {status === 'cancelled' && 'Đã hủy'}
                    </option>
                  ))}
                </select>
              </div>
              
              {transitionData.to_status === 'cancelled' && (
                <div className="mb-4">
                  <label className="block text-sm font-medium text-gray-700 mb-2">Lý do hủy</label>
                  <textarea
                    value={transitionData.reason}
                    onChange={(e) => setTransitionData({...transitionData, reason: e.target.value})}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    rows="3"
                    placeholder="Nhập lý do hủy bỏ..."
                  />
                </div>
              )}
              
              <div className="flex space-x-3">
                <button
                  onClick={() => {
                    setShowTransitionModal(false);
                    setTransitionData({ to_status: '', reason: '' });
                  }}
                  className="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Hủy
                </button>
                
                <button
                  onClick={handleTransition}
                  disabled={transitionLoading || !transitionData.to_status}
                  className="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {transitionLoading ? 'Đang chuyển...' : 'Xác nhận'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BatchDetailPage;
