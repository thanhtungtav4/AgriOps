import React, { useEffect, useMemo, useState } from 'react';
import api from '../../../services/api';

const today = () => new Date().toISOString().slice(0, 10);

const formatDate = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('vi-VN');
};

const unwrap = (response) => {
  const data = response.data.data || response.data || [];
  return Array.isArray(data) ? data : (data.data || []);
};

const SafetyPage = () => {
  const [activeTab, setActiveTab] = useState('incidents');
  const [incidents, setIncidents] = useState([]);
  const [usages, setUsages] = useState([]);
  const [products, setProducts] = useState([]);
  const [lowStock, setLowStock] = useState([]);
  const [batches, setBatches] = useState([]);
  const [farms, setFarms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [incidentForm, setIncidentForm] = useState({
    farm_id: '',
    planting_batch_id: '',
    incident_type: 'pest',
    severity: 'medium',
    detected_at: today(),
    description: '',
    treatment_note: '',
  });

  const [usageForm, setUsageForm] = useState({
    farm_id: '',
    incident_id: '',
    planting_batch_id: '',
    product_name: '',
    product_type: 'biological',
    active_ingredient: '',
    dosage_value: '',
    dosage_unit: 'ml/L',
    quantity_value: '',
    quantity_unit: 'L',
    cost_amount: '',
    isolation_days: '',
    applied_at: today(),
    notes: '',
  });

  const [productForm, setProductForm] = useState({
    farm_id: '',
    name: '',
    active_ingredient: '',
    type: 'biological',
    unit: 'L',
    stock_quantity: '',
    min_stock_level: '',
    price_per_unit: '',
    expiry_date: '',
    notes: '',
  });

  const loadData = async () => {
    setLoading(true);
    setError(null);

    try {
      const [incidentRes, usageRes, productRes, lowStockRes, batchRes, farmRes] = await Promise.all([
        api.get('/incidents'),
        api.get('/chemical-usages'),
        api.get('/chemical-products'),
        api.get('/chemical-products/low-stock'),
        api.get('/planting-batches'),
        api.get('/farms'),
      ]);
      setIncidents(unwrap(incidentRes));
      setUsages(unwrap(usageRes));
      setProducts(unwrap(productRes));
      setLowStock(unwrap(lowStockRes));
      setBatches(unwrap(batchRes));
      setFarms(unwrap(farmRes));
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải dữ liệu an toàn sản xuất.');
      console.error('Error loading safety data:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const activeIsolation = useMemo(() => usages.filter((usage) => {
    if (!usage.isolation_ends_at) return false;
    return new Date(usage.isolation_ends_at) >= new Date();
  }), [usages]);

  const cleanPayload = (payload) => {
    const next = { ...payload };
    Object.keys(next).forEach((key) => {
      if (next[key] === '' || next[key] === undefined || next[key] === null) delete next[key];
    });
    return next;
  };

  const submitIncident = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const response = await api.post('/incidents', cleanPayload(incidentForm));
      setIncidents((prev) => [response.data.data, ...prev]);
      setIncidentForm({
        farm_id: '',
        planting_batch_id: '',
        incident_type: 'pest',
        severity: 'medium',
        detected_at: today(),
        description: '',
        treatment_note: '',
      });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể ghi nhận sự cố.');
      console.error('Error creating incident:', err);
    } finally {
      setSaving(false);
    }
  };

  const submitUsage = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const response = await api.post('/chemical-usages', cleanPayload({
        ...usageForm,
        dosage_value: usageForm.dosage_value ? parseFloat(usageForm.dosage_value) : '',
        quantity_value: usageForm.quantity_value ? parseFloat(usageForm.quantity_value) : '',
        cost_amount: usageForm.cost_amount ? parseFloat(usageForm.cost_amount) : '',
        isolation_days: usageForm.isolation_days ? parseInt(usageForm.isolation_days, 10) : '',
      }));
      setUsages((prev) => [response.data.data, ...prev]);
      setUsageForm({
        farm_id: '',
        incident_id: '',
        planting_batch_id: '',
        product_name: '',
        product_type: 'biological',
        active_ingredient: '',
        dosage_value: '',
        dosage_unit: 'ml/L',
        quantity_value: '',
        quantity_unit: 'L',
        cost_amount: '',
        isolation_days: '',
        applied_at: today(),
        notes: '',
      });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể ghi nhận sử dụng thuốc/sinh học.');
      console.error('Error creating chemical usage:', err);
    } finally {
      setSaving(false);
    }
  };

  const submitProduct = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const response = await api.post('/chemical-products', cleanPayload({
        ...productForm,
        stock_quantity: productForm.stock_quantity ? parseFloat(productForm.stock_quantity) : 0,
        min_stock_level: productForm.min_stock_level ? parseFloat(productForm.min_stock_level) : 0,
        price_per_unit: productForm.price_per_unit ? parseFloat(productForm.price_per_unit) : '',
        is_active: true,
      }));
      setProducts((prev) => [response.data.data, ...prev]);
      setProductForm({
        farm_id: '',
        name: '',
        active_ingredient: '',
        type: 'biological',
        unit: 'L',
        stock_quantity: '',
        min_stock_level: '',
        price_per_unit: '',
        expiry_date: '',
        notes: '',
      });
      await loadData();
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tạo vật tư an toàn.');
      console.error('Error creating chemical product:', err);
    } finally {
      setSaving(false);
    }
  };

  const stockStatus = (product) => {
    const stock = Number(product.stock_quantity || 0);
    const min = Number(product.min_stock_level || 0);
    if (min > 0 && stock < min) return 'Thiếu tồn';
    if (product.expiry_date && new Date(product.expiry_date) < new Date()) return 'Hết hạn';
    return 'Ổn';
  };

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-gray-800">An toàn sản xuất</h1>
        <p className="text-gray-600">Theo dõi sự cố, sử dụng thuốc/sinh học và thời gian cách ly trước thu hoạch.</p>
      </div>

      <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div className="bg-white border border-gray-200 rounded-lg p-3">
          <div className="text-xs text-gray-500">Sự cố mở</div>
          <div className="text-2xl font-semibold text-gray-800">{incidents.filter((item) => item.status === 'open').length}</div>
        </div>
        <div className="bg-white border border-gray-200 rounded-lg p-3">
          <div className="text-xs text-gray-500">Đang cách ly</div>
          <div className="text-2xl font-semibold text-amber-700">{activeIsolation.length}</div>
        </div>
        <div className="bg-white border border-gray-200 rounded-lg p-3">
          <div className="text-xs text-gray-500">Vật tư thấp</div>
          <div className="text-2xl font-semibold text-red-700">{lowStock.length}</div>
        </div>
        <div className="bg-white border border-gray-200 rounded-lg p-3">
          <div className="text-xs text-gray-500">Sản phẩm</div>
          <div className="text-2xl font-semibold text-gray-800">{products.length}</div>
        </div>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>}

      <div className="bg-white rounded-lg shadow border border-gray-200">
        <div className="border-b border-gray-200 p-2 flex gap-2 overflow-x-auto">
          {[
            ['incidents', 'Sự cố'],
            ['usages', 'Thuốc/sinh học'],
            ['products', 'Vật tư'],
            ['isolation', 'Cách ly'],
          ].map(([id, label]) => (
            <button
              key={id}
              type="button"
              onClick={() => setActiveTab(id)}
              className={`px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap ${
                activeTab === id ? 'bg-emerald-100 text-emerald-800' : 'text-gray-600 hover:bg-gray-100'
              }`}
            >
              {label}
            </button>
          ))}
        </div>

        {loading ? (
          <div className="p-8 text-center text-gray-600">Đang tải dữ liệu...</div>
        ) : (
          <div className="p-4">
            {activeTab === 'incidents' && (
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <form onSubmit={submitIncident} className="space-y-3">
                  <h2 className="font-medium text-gray-800">Ghi nhận sự cố</h2>
                  <select value={incidentForm.farm_id} onChange={(e) => setIncidentForm((p) => ({ ...p, farm_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Farm của tài khoản</option>
                    {farms.map((farm) => <option key={farm.id} value={farm.id}>{farm.name}</option>)}
                  </select>
                  <select value={incidentForm.planting_batch_id} onChange={(e) => setIncidentForm((p) => ({ ...p, planting_batch_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Không gắn lứa</option>
                    {batches.map((batch) => <option key={batch.id} value={batch.id}>{batch.code || `Lứa #${batch.id}`}</option>)}
                  </select>
                  <div className="grid grid-cols-2 gap-2">
                    <select value={incidentForm.incident_type} onChange={(e) => setIncidentForm((p) => ({ ...p, incident_type: e.target.value }))} className="px-3 py-2 border rounded-lg bg-white">
                      <option value="pest">Sâu hại</option>
                      <option value="disease">Bệnh</option>
                      <option value="weather">Thời tiết</option>
                      <option value="soil">Đất</option>
                      <option value="other">Khác</option>
                    </select>
                    <select value={incidentForm.severity} onChange={(e) => setIncidentForm((p) => ({ ...p, severity: e.target.value }))} className="px-3 py-2 border rounded-lg bg-white">
                      <option value="low">Nhẹ</option>
                      <option value="medium">Trung bình</option>
                      <option value="high">Nặng</option>
                      <option value="critical">Nghiêm trọng</option>
                    </select>
                  </div>
                  <input type="date" value={incidentForm.detected_at} onChange={(e) => setIncidentForm((p) => ({ ...p, detected_at: e.target.value }))} className="w-full px-3 py-2 border rounded-lg" />
                  <textarea value={incidentForm.description} onChange={(e) => setIncidentForm((p) => ({ ...p, description: e.target.value }))} rows="3" placeholder="Mô tả sự cố..." className="w-full px-3 py-2 border rounded-lg" />
                  <textarea value={incidentForm.treatment_note} onChange={(e) => setIncidentForm((p) => ({ ...p, treatment_note: e.target.value }))} rows="2" placeholder="Hướng xử lý..." className="w-full px-3 py-2 border rounded-lg" />
                  <button disabled={saving} className="px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50">{saving ? 'Đang lưu...' : 'Lưu sự cố'}</button>
                </form>
                <div className="lg:col-span-2 overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Loại</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Lứa</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Mức độ</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Ngày</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Trạng thái</th></tr></thead>
                    <tbody className="divide-y divide-gray-200">
                      {incidents.map((item) => (
                        <tr key={item.id}><td className="px-3 py-2">{item.incident_type}</td><td className="px-3 py-2">{item.planting_batch?.code || item.planting_batch_id || '-'}</td><td className="px-3 py-2">{item.severity}</td><td className="px-3 py-2">{formatDate(item.detected_at)}</td><td className="px-3 py-2">{item.status}</td></tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {activeTab === 'usages' && (
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <form onSubmit={submitUsage} className="space-y-3">
                  <h2 className="font-medium text-gray-800">Ghi nhận sử dụng</h2>
                  <select value={usageForm.incident_id} onChange={(e) => setUsageForm((p) => ({ ...p, incident_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Không gắn sự cố</option>
                    {incidents.map((incident) => <option key={incident.id} value={incident.id}>#{incident.id} - {incident.incident_type}</option>)}
                  </select>
                  <select value={usageForm.planting_batch_id} onChange={(e) => setUsageForm((p) => ({ ...p, planting_batch_id: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="">Chọn lứa nếu không gắn sự cố</option>
                    {batches.map((batch) => <option key={batch.id} value={batch.id}>{batch.code || `Lứa #${batch.id}`}</option>)}
                  </select>
                  <input value={usageForm.product_name} onChange={(e) => setUsageForm((p) => ({ ...p, product_name: e.target.value }))} placeholder="Tên sản phẩm" className="w-full px-3 py-2 border rounded-lg" required />
                  <div className="grid grid-cols-2 gap-2">
                    <select value={usageForm.product_type} onChange={(e) => setUsageForm((p) => ({ ...p, product_type: e.target.value }))} className="px-3 py-2 border rounded-lg bg-white">
                      <option value="biological">Sinh học</option>
                      <option value="chemical">Hóa học</option>
                    </select>
                    <input value={usageForm.active_ingredient} onChange={(e) => setUsageForm((p) => ({ ...p, active_ingredient: e.target.value }))} placeholder="Hoạt chất" className="px-3 py-2 border rounded-lg" />
                  </div>
                  <div className="grid grid-cols-2 gap-2">
                    <input type="number" step="any" value={usageForm.quantity_value} onChange={(e) => setUsageForm((p) => ({ ...p, quantity_value: e.target.value }))} placeholder="Số lượng dùng" className="px-3 py-2 border rounded-lg" />
                    <input value={usageForm.quantity_unit} onChange={(e) => setUsageForm((p) => ({ ...p, quantity_unit: e.target.value }))} placeholder="Đơn vị" className="px-3 py-2 border rounded-lg" />
                  </div>
                  <div className="grid grid-cols-2 gap-2">
                    <input type="number" min="0" value={usageForm.isolation_days} onChange={(e) => setUsageForm((p) => ({ ...p, isolation_days: e.target.value }))} placeholder="Ngày cách ly" className="px-3 py-2 border rounded-lg" />
                    <input type="date" value={usageForm.applied_at} onChange={(e) => setUsageForm((p) => ({ ...p, applied_at: e.target.value }))} className="px-3 py-2 border rounded-lg" />
                  </div>
                  <textarea value={usageForm.notes} onChange={(e) => setUsageForm((p) => ({ ...p, notes: e.target.value }))} rows="3" placeholder="Ghi chú..." className="w-full px-3 py-2 border rounded-lg" />
                  <button disabled={saving} className="px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50">{saving ? 'Đang lưu...' : 'Lưu sử dụng'}</button>
                </form>
                <div className="lg:col-span-2 overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Sản phẩm</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Lứa</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Ngày dùng</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Hết cách ly</th></tr></thead>
                    <tbody className="divide-y divide-gray-200">
                      {usages.map((item) => (
                        <tr key={item.id}><td className="px-3 py-2">{item.product_name}</td><td className="px-3 py-2">{item.planting_batch?.code || item.planting_batch_id || '-'}</td><td className="px-3 py-2">{formatDate(item.applied_at)}</td><td className="px-3 py-2">{formatDate(item.isolation_ends_at)}</td></tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {activeTab === 'products' && (
              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <form onSubmit={submitProduct} className="space-y-3">
                  <h2 className="font-medium text-gray-800">Thêm vật tư</h2>
                  <input value={productForm.name} onChange={(e) => setProductForm((p) => ({ ...p, name: e.target.value }))} placeholder="Tên sản phẩm" className="w-full px-3 py-2 border rounded-lg" required />
                  <input value={productForm.active_ingredient} onChange={(e) => setProductForm((p) => ({ ...p, active_ingredient: e.target.value }))} placeholder="Hoạt chất" className="w-full px-3 py-2 border rounded-lg" required />
                  <select value={productForm.type} onChange={(e) => setProductForm((p) => ({ ...p, type: e.target.value }))} className="w-full px-3 py-2 border rounded-lg bg-white">
                    <option value="pesticide">Thuốc sâu</option><option value="herbicide">Thuốc cỏ</option><option value="fungicide">Thuốc nấm</option><option value="fertilizer">Phân bón</option><option value="biological">Sinh học</option><option value="other">Khác</option>
                  </select>
                  <div className="grid grid-cols-3 gap-2">
                    <input value={productForm.unit} onChange={(e) => setProductForm((p) => ({ ...p, unit: e.target.value }))} placeholder="Đơn vị" className="px-3 py-2 border rounded-lg" required />
                    <input type="number" step="any" value={productForm.stock_quantity} onChange={(e) => setProductForm((p) => ({ ...p, stock_quantity: e.target.value }))} placeholder="Tồn" className="px-3 py-2 border rounded-lg" />
                    <input type="number" step="any" value={productForm.min_stock_level} onChange={(e) => setProductForm((p) => ({ ...p, min_stock_level: e.target.value }))} placeholder="Tồn tối thiểu" className="px-3 py-2 border rounded-lg" />
                  </div>
                  <input type="date" value={productForm.expiry_date} onChange={(e) => setProductForm((p) => ({ ...p, expiry_date: e.target.value }))} className="w-full px-3 py-2 border rounded-lg" />
                  <button disabled={saving} className="px-4 py-2 bg-emerald-600 text-white rounded-lg disabled:opacity-50">{saving ? 'Đang lưu...' : 'Lưu vật tư'}</button>
                </form>
                <div className="lg:col-span-2 overflow-x-auto">
                  <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Tên</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Loại</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Tồn</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Hạn</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Trạng thái</th></tr></thead>
                    <tbody className="divide-y divide-gray-200">
                      {products.map((item) => (
                        <tr key={item.id}><td className="px-3 py-2">{item.name}</td><td className="px-3 py-2">{item.type}</td><td className="px-3 py-2">{item.stock_quantity} {item.unit}</td><td className="px-3 py-2">{formatDate(item.expiry_date)}</td><td className="px-3 py-2">{stockStatus(item)}</td></tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {activeTab === 'isolation' && (
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50"><tr><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Sản phẩm</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Lứa</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Ngày dùng</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Hết cách ly</th><th className="px-3 py-2 text-left text-xs uppercase text-gray-500">Ghi chú</th></tr></thead>
                  <tbody className="divide-y divide-gray-200">
                    {activeIsolation.map((item) => (
                      <tr key={item.id}><td className="px-3 py-2">{item.product_name}</td><td className="px-3 py-2">{item.planting_batch?.code || item.planting_batch_id || '-'}</td><td className="px-3 py-2">{formatDate(item.applied_at)}</td><td className="px-3 py-2 text-amber-700 font-medium">{formatDate(item.isolation_ends_at)}</td><td className="px-3 py-2">{item.notes || '-'}</td></tr>
                    ))}
                    {activeIsolation.length === 0 && <tr><td colSpan="5" className="px-3 py-8 text-center text-gray-500">Không có lứa đang cách ly.</td></tr>}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default SafetyPage;

