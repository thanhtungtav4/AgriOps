import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import api from '../../services/api';

const CreateBatchPage = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const planId = searchParams.get('plan_id');
  
  const [formData, setFormData] = useState({
    crop_id: '',
    variety_id: '',
    farm_id: '',
    planned_quantity: '',
    planned_unit: 'kg',
    planned_area_m2: '',
    planned_start_date: '',
    planned_harvest_date: '',
    notes: ''
  });
  
  const [productionPlan, setProductionPlan] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [submitLoading, setSubmitLoading] = useState(false);

  useEffect(() => {
    const loadPlan = async () => {
      if (!planId) {
        setLoading(false);
        return;
      }

      if (planId) {
        try {
          const response = await api.get('/production-plans');
          const plans = response.data.data || response.data || [];
          const plan = plans.find((item) => String(item.id) === String(planId));

          if (!plan) {
            setError('Không tìm thấy kế hoạch sản xuất. Vui lòng chọn lại từ danh sách kế hoạch.');
            setLoading(false);
            return;
          }

          setProductionPlan(plan);
          
          // Pre-fill form with plan data
          setFormData(prev => ({
            ...prev,
            crop_id: plan.crop_id,
            variety_id: plan.variety_id || '',
            farm_id: plan.farm_id,
            planned_quantity: plan.quantity,
            planned_unit: plan.unit,
            planned_harvest_date: plan.target_delivery_date || ''
          }));
        } catch (err) {
          setError(err.response?.data?.error?.message || 'Không thể tải kế hoạch sản xuất. Vui lòng thử lại.');
          console.error('Error loading production plan:', err);
        }
      }
      setLoading(false);
    };

    loadPlan();
  }, [planId]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitLoading(true);
    setError(null);
    
    try {
      const payload = {
        ...formData,
        planned_quantity: parseFloat(formData.planned_quantity),
        production_plan_id: planId ? parseInt(planId, 10) : undefined
      };

      if (formData.planned_area_m2) {
        payload.planned_area_m2 = parseFloat(formData.planned_area_m2);
      } else {
        delete payload.planned_area_m2;
      }
      
      // Remove empty values
      Object.keys(payload).forEach(key => {
        if (payload[key] === '') delete payload[key];
        if (payload[key] === undefined || payload[key] === null) delete payload[key];
      });
      
      const response = await api.post('/planting-batches', payload);
      
      // Navigate to the batch detail page
      navigate(`/operations/batches/${response.data.data.id}`);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tạo lứa trồng. Vui lòng thử lại.');
      console.error('Error creating batch:', err);
    } finally {
      setSubmitLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="max-w-3xl mx-auto">
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
          
          <h1 className="text-2xl font-semibold text-gray-800">Tạo lứa trồng mới</h1>
        </div>
        
        <div className="bg-white rounded-lg shadow">
          <div className="p-6">
            <div className="animate-pulse flex items-center justify-center h-32">
              <div className="text-center">
                <div className="w-8 h-8 border-4 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p className="text-gray-600">Đang tải dữ liệu...</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (error && !loading) {
    return (
      <div className="max-w-3xl mx-auto">
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
          
          <h1 className="text-2xl font-semibold text-gray-800">Tạo lứa trồng mới</h1>
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

  if (!planId) {
    return (
      <div className="max-w-3xl mx-auto">
        <div className="mb-6">
          <button 
            onClick={() => navigate('/operations/plans')}
            className="flex items-center text-emerald-600 hover:text-emerald-800 mb-4"
          >
            <svg className="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
            </svg>
            Về danh sách kế hoạch
          </button>

          <h1 className="text-2xl font-semibold text-gray-800">Tạo lứa trồng mới</h1>
          <p className="text-gray-600">Lứa trồng nên được tạo từ một kế hoạch sản xuất đã lưu.</p>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <p className="text-gray-700 mb-4">Chọn một kế hoạch sản xuất để hệ thống tự điền cây trồng, farm, sản lượng và ngày thu hoạch mục tiêu.</p>
          <button
            onClick={() => navigate('/operations/plans')}
            className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
          >
            Chọn kế hoạch sản xuất
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="max-w-3xl mx-auto">
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
        
        <h1 className="text-2xl font-semibold text-gray-800">Tạo lứa trồng mới</h1>
        <p className="text-gray-600">Tạo lứa trồng từ kế hoạch sản xuất</p>
      </div>
      
      {productionPlan && (
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <h3 className="font-medium text-blue-800 mb-2">Tạo từ kế hoạch sản xuất</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div><span className="text-blue-600">Kế hoạch:</span> #{productionPlan.id}</div>
            <div><span className="text-blue-600">Cây trồng:</span> {productionPlan.crop?.name}</div>
            <div><span className="text-blue-600">Farm:</span> {productionPlan.farm?.name}</div>
            <div><span className="text-blue-600">Số lượng:</span> {productionPlan.quantity?.toLocaleString('vi-VN')} {productionPlan.unit}</div>
          </div>
        </div>
      )}
      
      <div className="bg-white rounded-lg shadow p-6">
        {error && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {error}
          </div>
        )}
        
        <form onSubmit={handleSubmit}>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Left column */}
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Cây trồng *</label>
                <select
                  name="crop_id"
                  value={formData.crop_id}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  required
                  disabled={!!productionPlan}
                >
                  <option value="">Chọn cây trồng</option>
                  <option value={productionPlan?.crop_id}>{productionPlan?.crop?.name}</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Farm *</label>
                <select
                  name="farm_id"
                  value={formData.farm_id}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  required
                  disabled={!!productionPlan}
                >
                  <option value="">Chọn farm</option>
                  <option value={productionPlan?.farm_id}>{productionPlan?.farm?.name}</option>
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">SL dự kiến *</label>
                <input
                  type="number"
                  name="planned_quantity"
                  value={formData.planned_quantity}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  placeholder="100"
                  step="any"
                  min="0"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Đơn vị *</label>
                <select
                  name="planned_unit"
                  value={formData.planned_unit}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  required
                >
                  <option value="kg">kg</option>
                  <option value="trái">trái</option>
                  <option value="bó">bó</option>
                  <option value="thùng">thùng</option>
                </select>
              </div>
            </div>
            
            {/* Right column */}
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Diện tích (m²)</label>
                <input
                  type="number"
                  name="planned_area_m2"
                  value={formData.planned_area_m2}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  placeholder="50"
                  step="any"
                  min="0"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu *</label>
                <input
                  type="date"
                  name="planned_start_date"
                  value={formData.planned_start_date}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  required
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Ngày thu hoạch</label>
                <input
                  type="date"
                  name="planned_harvest_date"
                  value={formData.planned_harvest_date}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
                <textarea
                  name="notes"
                  value={formData.notes}
                  onChange={handleInputChange}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  rows="3"
                  placeholder="Ghi chú về lứa trồng..."
                />
              </div>
            </div>
          </div>
          
          <div className="mt-6 flex space-x-3">
            <button
              type="submit"
              disabled={submitLoading}
              className="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {submitLoading ? 'Đang tạo...' : 'Tạo lứa trồng'}
            </button>
            
            <button
              type="button"
              onClick={() => navigate(-1)}
              className="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Hủy
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CreateBatchPage;
