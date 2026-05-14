import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import api from '../../services/api';

const PlanningCalculator = () => {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const [farms, setFarms] = useState([]);
  const [crops, setCrops] = useState([]);
  const [varieties, setVarieties] = useState([]);
  const [selectedCropId, setSelectedCropId] = useState('');
  const [calculationResult, setCalculationResult] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [saveLoading, setSaveLoading] = useState(false);
  const [savedPlan, setSavedPlan] = useState(null);
  const [error, setError] = useState(null);
  
  const [formData, setFormData] = useState({
    farm_id: '',
    crop_id: '',
    variety_id: '',
    quantity: '',
    unit: 'kg',
    frequency: 'daily',
    target_date: '',
  });

  useEffect(() => {
    const nextFormData = {
      farm_id: searchParams.get('farm_id') || '',
      crop_id: searchParams.get('crop_id') || '',
      variety_id: searchParams.get('variety_id') || '',
      quantity: searchParams.get('quantity') || '',
      unit: searchParams.get('unit') || 'kg',
      frequency: searchParams.get('frequency') || 'daily',
      target_date: searchParams.get('target_date') || '',
    };

    if (nextFormData.crop_id || nextFormData.quantity || nextFormData.target_date) {
      setFormData(nextFormData);
      setSelectedCropId(nextFormData.crop_id);
      setCalculationResult(null);
      setSavedPlan(null);
    }
  }, [searchParams]);

  // Load farms, crops, and varieties on component mount
  useEffect(() => {
    const loadData = async () => {
      try {
        setIsLoading(true);
        
        // Load farms
        const farmsResponse = await api.get('/farms');
        setFarms(farmsResponse.data.data || farmsResponse.data || []);
        
        // Load crops
        const cropsResponse = await api.get('/crops');
        setCrops(cropsResponse.data.data || cropsResponse.data || []);
      } catch (err) {
        setError('Không thể tải dữ liệu. Vui lòng thử lại.');
        console.error('Error loading data:', err);
      } finally {
        setIsLoading(false);
      }
    };
    
    loadData();
  }, []);

  // Load varieties when crop is selected
  useEffect(() => {
    if (selectedCropId) {
      const loadVarieties = async () => {
        try {
          const varietiesResponse = await api.get(`/crop-varieties?crop_id=${selectedCropId}`);
          setVarieties(varietiesResponse.data.data || varietiesResponse.data || []);
        } catch (err) {
          console.error('Error loading varieties:', err);
        }
      };
      
      loadVarieties();
    } else {
      setVarieties([]);
    }
  }, [selectedCropId]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
    
    // Reset varieties when crop changes
    if (name === 'crop_id') {
      setSelectedCropId(value);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);
    
    try {
      // Prepare payload - only include variety_id if it's selected
      const payload = {
        farm_id: formData.farm_id,
        crop_id: formData.crop_id,
        quantity: parseFloat(formData.quantity),
        unit: formData.unit,
        frequency: formData.frequency,
        target_date: formData.target_date,
      };
      
      if (formData.variety_id) {
        payload.variety_id = formData.variety_id;
      }
      
      // Make API call to planning calculate
      const response = await api.post('/planning/calculate', payload);
      setCalculationResult(response.data);
      setSavedPlan(null);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tính toán kế hoạch. Vui lòng thử lại.');
      console.error('Error calculating plan:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const resetForm = () => {
    setFormData({
      farm_id: '',
      crop_id: '',
      variety_id: '',
      quantity: '',
      unit: 'kg',
      frequency: 'daily',
      target_date: '',
    });
    setSelectedCropId('');
    setCalculationResult(null);
    setSavedPlan(null);
    setError(null);
  };

  const handleSavePlan = async () => {
    if (!calculationResult) return;

    setSaveLoading(true);
    setError(null);

    try {
      const payload = {
        crop_id: formData.crop_id,
        farm_id: formData.farm_id,
        supply_contract_id: searchParams.get('supply_contract_id') || undefined,
        supply_demand_id: searchParams.get('supply_demand_id') || undefined,
        quantity: parseFloat(formData.quantity),
        unit: formData.unit,
        target_delivery_date: formData.target_date,
        estimated_cost: calculationResult.output?.estimated_cost,
        estimated_revenue: calculationResult.output?.estimated_revenue,
        margin_percent: calculationResult.output?.margin_percent,
        status: 'draft',
        notes: `Tạo từ màn tính kế hoạch (${formData.frequency})`,
      };

      if (formData.variety_id) {
        payload.variety_id = formData.variety_id;
      }

      Object.keys(payload).forEach((key) => {
        if (payload[key] === undefined || payload[key] === null || payload[key] === '') {
          delete payload[key];
        }
      });

      const response = await api.post('/production-plans', payload);
      setSavedPlan(response.data.data);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể lưu kế hoạch sản xuất. Vui lòng thử lại.');
      console.error('Error saving production plan:', err);
    } finally {
      setSaveLoading(false);
    }
  };

  // Output card components
  const OutputCard = ({ title, value, unit, description }) => (
    <div className="bg-white rounded-lg shadow p-4 border border-gray-200">
      <h3 className="text-sm font-medium text-gray-500 mb-1">{title}</h3>
      <div className="text-2xl font-bold text-emerald-700">
        {typeof value === 'number' ? value.toFixed(2) : value}
        {unit && <span className="text-sm font-normal ml-1 text-gray-500">{unit}</span>}
      </div>
      {description && <p className="text-xs text-gray-400 mt-1">{description}</p>}
    </div>
  );

  return (
    <div className="max-w-6xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-gray-800">Lập kế hoạch sản xuất</h1>
        <p className="text-gray-600">Nhập thông tin nhu cầu để hệ thống tính toán kế hoạch trồng phù hợp</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Input Form */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-lg shadow p-6">
            <h2 className="text-lg font-medium text-gray-800 mb-4">Thông tin yêu cầu</h2>
            
            {error && (
              <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {error}
              </div>
            )}
            
            <form onSubmit={handleSubmit}>
              <div className="space-y-4">
                {/* Farm selection */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Farm</label>
                  <select
                    name="farm_id"
                    value={formData.farm_id}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    required
                  >
                    <option value="">Chọn farm</option>
                    {farms.map(farm => (
                      <option key={farm.id} value={farm.id}>{farm.name}</option>
                    ))}
                  </select>
                </div>
                
                {/* Crop selection */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Cây trồng</label>
                  <select
                    name="crop_id"
                    value={formData.crop_id}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    required
                  >
                    <option value="">Chọn cây trồng</option>
                    {crops.map(crop => (
                      <option key={crop.id} value={crop.id}>{crop.name}</option>
                    ))}
                  </select>
                </div>
                
                {/* Variety selection */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Giống (tùy chọn)</label>
                  <select
                    name="variety_id"
                    value={formData.variety_id}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    disabled={!selectedCropId}
                  >
                    <option value="">Chọn giống</option>
                    {varieties.map(variety => (
                      <option key={variety.id} value={variety.id}>{variety.name}</option>
                    ))}
                  </select>
                </div>
                
                {/* Quantity and unit */}
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                    <input
                      type="number"
                      name="quantity"
                      value={formData.quantity}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                      placeholder="10"
                      required
                      min="0"
                      step="any"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Đơn vị</label>
                    <select
                      name="unit"
                      value={formData.unit}
                      onChange={handleInputChange}
                      className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    >
                      <option value="kg">kg</option>
                      <option value="trái">trái</option>
                      <option value="bó">bó</option>
                      <option value="thùng">thùng</option>
                    </select>
                  </div>
                </div>
                
                {/* Frequency */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Tần suất</label>
                  <select
                    name="frequency"
                    value={formData.frequency}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                  >
                    <option value="once">Một lần</option>
                    <option value="daily">Mỗi ngày</option>
                    <option value="weekly">Mỗi tuần</option>
                    <option value="monthly">Mỗi tháng</option>
                    <option value="seasonal">Theo mùa</option>
                  </select>
                </div>
                
                {/* Target delivery date */}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Ngày giao mục tiêu</label>
                  <input
                    type="date"
                    name="target_date"
                    value={formData.target_date}
                    onChange={handleInputChange}
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    required
                  />
                </div>
              </div>
              
              <div className="mt-6 flex space-x-3">
                <button
                  type="submit"
                  disabled={isLoading}
                  className="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                  {isLoading ? 'Đang tính toán...' : 'Tính toán kế hoạch'}
                </button>
                
                <button
                  type="button"
                  onClick={resetForm}
                  className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                >
                  Xóa
                </button>
              </div>
            </form>
          </div>
        </div>
        
        {/* Results */}
        <div className="lg:col-span-2">
          {calculationResult ? (
            <div>
              <div className="flex justify-between items-center mb-4">
                <h2 className="text-lg font-medium text-gray-800">Kết quả tính toán</h2>
                <div className="text-sm text-gray-500">
                  {calculationResult.assumptions?.season_factor ? `Hệ số mùa vụ: ${calculationResult.assumptions.season_factor}` : ''}
                </div>
              </div>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                {/* Finished quantity */}
                <OutputCard 
                  title="Sản lượng giao khách" 
                  value={calculationResult.output?.delivery_quantity} 
                  unit={formData.unit}
                  description="Sản lượng thành phẩm cần giao"
                />
                
                {/* Raw harvest needed */}
                <OutputCard 
                  title="Sản lượng cần thu thô" 
                  value={calculationResult.output?.raw_harvest_quantity} 
                  unit={formData.unit}
                  description="Sản lượng cần thu thô để đạt mục tiêu"
                />
                
                {/* Plants needed */}
                <OutputCard 
                  title="Số cây cần trồng" 
                  value={calculationResult.output?.plants_to_plant_execution || calculationResult.output?.plants_to_plant_estimate} 
                  unit="cây"
                  description="Số cây cần trồng để đạt mục tiêu"
                />
                
                {/* Area needed */}
                <OutputCard 
                  title="Diện tích cần dùng" 
                  value={calculationResult.output?.area_m2} 
                  unit="m²"
                  description="Diện tích đất cần sử dụng"
                />
                
                {/* Cycles/batches */}
                <OutputCard 
                  title="Lứa trồng cần có" 
                  value={calculationResult.output?.batches_count || 1} 
                  unit="lứa"
                  description="Số lứa trồng cần duy trì"
                />
                
                {/* Labor estimate */}
                <OutputCard 
                  title="Công lao động dự kiến" 
                  value={calculationResult.output?.labor_hours} 
                  unit="giờ"
                  description="Tổng số giờ công dự kiến"
                />
                
                {/* Water estimate */}
                <OutputCard 
                  title="Nhu cầu nước" 
                  value={calculationResult.output?.water_requirement || 0} 
                  unit="L"
                  description="Lượng nước dự kiến cần dùng"
                />
                
                {/* Loss estimate */}
                <OutputCard 
                  title="Tỷ lệ hao hụt" 
                  value={(calculationResult.output?.total_loss_percentage || 0)} 
                  unit="%"
                  description="Tỷ lệ hao hụt dự kiến tổng"
                />
                
                {/* Estimated margin */}
                {calculationResult.output?.margin_percent && (
                  <OutputCard 
                    title="Lợi nhuận dự kiến" 
                    value={calculationResult.output.margin_percent} 
                    unit="%"
                    description="Biên lợi nhuận dự kiến"
                  />
                )}
                
                {/* Days to first harvest */}
                <OutputCard 
                  title="Ngày đến thu hoạch đầu tiên" 
                  value={calculationResult.output?.days_to_first_harvest} 
                  unit="ngày"
                  description="Số ngày từ trồng đến thu hoạch đầu tiên"
                />
                
                {/* Estimated planting date */}
                <OutputCard 
                  title="Ngày trồng dự kiến" 
                  value={calculationResult.output?.estimated_planting_date ? new Date(calculationResult.output.estimated_planting_date).toLocaleDateString('vi-VN') : ''} 
                  unit=""
                  description="Ngày trồng dự kiến"
                />
                
                {/* Estimated revenue */}
                {calculationResult.output?.estimated_revenue && (
                  <OutputCard 
                    title="Doanh thu dự kiến" 
                    value={calculationResult.output.estimated_revenue?.toLocaleString('vi-VN')} 
                    unit="VNĐ"
                    description="Doanh thu dự kiến theo giá thị trường"
                  />
                )}
              </div>
              
              {/* Fulfillment status */}
              {calculationResult.fulfillment && (
                <div className="bg-white rounded-lg shadow p-4 border border-gray-200">
                  <h3 className="text-sm font-medium text-gray-700 mb-2">Tình trạng đáp ứng</h3>
                  <div className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
                    calculationResult.fulfillment.status === 'ok' 
                      ? 'bg-green-100 text-green-800' 
                      : calculationResult.fulfillment.status === 'warning' 
                        ? 'bg-yellow-100 text-yellow-800' 
                        : 'bg-red-100 text-red-800'
                  }`}>
                    {calculationResult.fulfillment.status === 'ok' && 'Đáp ứng tốt'}
                    {calculationResult.fulfillment.status === 'warning' && 'Cảnh báo'}
                    {calculationResult.fulfillment.status === 'error' && 'Thiếu hụt'}
                  </div>
                  
                  {calculationResult.fulfillment.warnings && calculationResult.fulfillment.warnings.length > 0 && (
                    <div className="mt-2">
                      <p className="text-xs text-gray-600">Cảnh báo:</p>
                      <ul className="text-xs text-gray-700">
                        {calculationResult.fulfillment.warnings.map((warning, idx) => (
                          <li key={idx}>• {warning.replace(/_/g, ' ')}</li>
                        ))}
                      </ul>
                    </div>
                  )}
                </div>
              )}

              <div className="mt-6 bg-white rounded-lg shadow p-4 border border-gray-200">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                  <div>
                    <h3 className="text-sm font-medium text-gray-800">Chuyển kết quả thành kế hoạch sản xuất</h3>
                    <p className="text-xs text-gray-500 mt-1">Lưu kế hoạch để tạo lứa trồng và theo dõi thực thi.</p>
                  </div>
                  <div className="flex flex-col sm:flex-row gap-2">
                    <button
                      type="button"
                      onClick={handleSavePlan}
                      disabled={saveLoading || !!savedPlan}
                      className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                      {savedPlan ? 'Đã lưu kế hoạch' : saveLoading ? 'Đang lưu...' : 'Lưu kế hoạch'}
                    </button>
                    <button
                      type="button"
                      onClick={() => navigate('/operations/plans')}
                      className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition"
                    >
                      Xem kế hoạch
                    </button>
                  </div>
                </div>

                {savedPlan && (
                  <div className="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                      <p className="text-sm text-emerald-800">Đã tạo kế hoạch #{savedPlan.id}. Bước tiếp theo là tạo lứa trồng từ kế hoạch này.</p>
                      <button
                        type="button"
                        onClick={() => navigate(`/operations/batches/create?plan_id=${savedPlan.id}`)}
                        className="px-4 py-2 bg-emerald-700 text-white rounded-lg hover:bg-emerald-800 transition text-sm"
                      >
                        Tạo lứa trồng
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </div>
          ) : (
            <div className="bg-white rounded-lg shadow p-8 text-center">
              <div className="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg className="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <h3 className="text-lg font-medium text-gray-900 mb-1">Chưa có kết quả</h3>
              <p className="text-gray-500">Nhập thông tin yêu cầu và nhấn "Tính toán kế hoạch" để xem kết quả</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default PlanningCalculator;
