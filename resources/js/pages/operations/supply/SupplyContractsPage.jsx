import React, { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../../services/api';

const today = () => new Date().toISOString().slice(0, 10);

const formatDate = (value) => {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('vi-VN');
};

const SupplyContractsPage = () => {
  const navigate = useNavigate();
  const [contracts, setContracts] = useState([]);
  const [farms, setFarms] = useState([]);
  const [crops, setCrops] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({
    farm_id: '',
    customer_name: '',
    customer_type: 'wholesale',
    crop_id: '',
    quantity: '',
    unit: 'kg',
    frequency: 'weekly',
    start_date: today(),
    end_date: '',
    notes: '',
  });

  const loadData = async () => {
    setLoading(true);
    setError(null);

    try {
      const [contractsResponse, farmsResponse, cropsResponse] = await Promise.all([
        api.get('/supply-contracts'),
        api.get('/farms'),
        api.get('/crops'),
      ]);
      setContracts(contractsResponse.data.data || contractsResponse.data || []);
      setFarms(farmsResponse.data.data || farmsResponse.data || []);
      setCrops(cropsResponse.data.data || cropsResponse.data || []);
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tải hợp đồng cung ứng.');
      console.error('Error loading supply contracts:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadData();
  }, []);

  const activeContracts = useMemo(() => contracts.filter((item) => item.status !== 'cancelled'), [contracts]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError(null);

    try {
      const payload = {
        ...form,
        quantity: parseFloat(form.quantity),
        status: 'active',
      };

      Object.keys(payload).forEach((key) => {
        if (payload[key] === '' || payload[key] === undefined || payload[key] === null) delete payload[key];
      });

      const response = await api.post('/supply-contracts', payload);
      setContracts((prev) => [response.data.data, ...prev]);
      setShowForm(false);
      setForm({
        farm_id: '',
        customer_name: '',
        customer_type: 'wholesale',
        crop_id: '',
        quantity: '',
        unit: 'kg',
        frequency: 'weekly',
        start_date: today(),
        end_date: '',
        notes: '',
      });
    } catch (err) {
      setError(err.response?.data?.error?.message || 'Không thể tạo hợp đồng cung ứng.');
      console.error('Error creating supply contract:', err);
    } finally {
      setSaving(false);
    }
  };

  const planFromContract = (contract) => {
    const targetDate = contract.end_date || contract.start_date || today();
    const params = new URLSearchParams({
      source: 'contract',
      supply_contract_id: String(contract.id),
      farm_id: String(contract.farm_id || ''),
      crop_id: String(contract.crop_id || ''),
      quantity: String(contract.quantity || ''),
      unit: contract.unit || 'kg',
      frequency: contract.frequency || 'weekly',
      target_date: targetDate?.slice(0, 10),
    });

    navigate(`/operations/planning?${params.toString()}`);
  };

  return (
    <div className="max-w-7xl mx-auto">
      <div className="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 className="text-2xl font-semibold text-gray-800">Hợp đồng cung ứng</h1>
          <p className="text-gray-600">Quản lý nhu cầu đều đặn từ siêu thị/khách sỉ và chuyển thành kế hoạch sản xuất.</p>
        </div>
        <button
          type="button"
          onClick={() => setShowForm((value) => !value)}
          className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700"
        >
          {showForm ? 'Đóng form' : 'Tạo hợp đồng'}
        </button>
      </div>

      {error && <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">{error}</div>}

      {showForm && (
        <div className="mb-6 bg-white rounded-lg shadow border border-gray-200 p-6">
          <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Khách hàng</label>
              <input
                value={form.customer_name}
                onChange={(event) => setForm((prev) => ({ ...prev, customer_name: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Loại khách</label>
              <select
                value={form.customer_type}
                onChange={(event) => setForm((prev) => ({ ...prev, customer_type: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
              >
                <option value="restaurant">Nhà hàng</option>
                <option value="wholesale">Siêu thị/sỉ</option>
                <option value="retail">Bán lẻ</option>
                <option value="export">Xuất khẩu</option>
                <option value="other">Khác</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Farm</label>
              <select
                value={form.farm_id}
                onChange={(event) => setForm((prev) => ({ ...prev, farm_id: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
              >
                <option value="">Theo farm của tài khoản</option>
                {farms.map((farm) => (
                  <option key={farm.id} value={farm.id}>{farm.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Cây trồng</label>
              <select
                value={form.crop_id}
                onChange={(event) => setForm((prev) => ({ ...prev, crop_id: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
                required
              >
                <option value="">Chọn cây</option>
                {crops.map((crop) => (
                  <option key={crop.id} value={crop.id}>{crop.name}</option>
                ))}
              </select>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Số lượng</label>
                <input
                  type="number"
                  step="any"
                  min="0.01"
                  value={form.quantity}
                  onChange={(event) => setForm((prev) => ({ ...prev, quantity: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                  required
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Đơn vị</label>
                <select
                  value={form.unit}
                  onChange={(event) => setForm((prev) => ({ ...prev, unit: event.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
                >
                  <option value="kg">kg</option>
                  <option value="trái">trái</option>
                  <option value="bó">bó</option>
                  <option value="thùng">thùng</option>
                </select>
              </div>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tần suất</label>
              <select
                value={form.frequency}
                onChange={(event) => setForm((prev) => ({ ...prev, frequency: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-white"
              >
                <option value="once">Một lần</option>
                <option value="daily">Mỗi ngày</option>
                <option value="weekly">Mỗi tuần</option>
                <option value="monthly">Mỗi tháng</option>
                <option value="seasonal">Theo mùa</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Ngày bắt đầu</label>
              <input
                type="date"
                value={form.start_date}
                onChange={(event) => setForm((prev) => ({ ...prev, start_date: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Ngày kết thúc</label>
              <input
                type="date"
                value={form.end_date}
                onChange={(event) => setForm((prev) => ({ ...prev, end_date: event.target.value }))}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
              <textarea
                value={form.notes}
                onChange={(event) => setForm((prev) => ({ ...prev, notes: event.target.value }))}
                rows="3"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
              />
            </div>
            <div className="md:col-span-2">
              <button
                type="submit"
                disabled={saving}
                className="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50"
              >
                {saving ? 'Đang lưu...' : 'Lưu hợp đồng'}
              </button>
            </div>
          </form>
        </div>
      )}

      <div className="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        {loading ? (
          <div className="p-8 text-center text-gray-600">Đang tải hợp đồng...</div>
        ) : activeContracts.length === 0 ? (
          <div className="p-8 text-center text-gray-600">Chưa có hợp đồng cung ứng.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Khách</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cây</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nhu cầu</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tần suất</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời hạn</th>
                  <th className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {activeContracts.map((contract) => (
                  <tr key={contract.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3">
                      <div className="font-medium text-gray-900">{contract.customer_name}</div>
                      <div className="text-xs text-gray-500">{contract.customer_type || '-'}</div>
                    </td>
                    <td className="px-4 py-3 text-sm text-gray-700">{contract.crop?.name || '-'}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{Number(contract.quantity).toLocaleString('vi-VN')} {contract.unit}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{contract.frequency || '-'}</td>
                    <td className="px-4 py-3 text-sm text-gray-700">{formatDate(contract.start_date)} - {formatDate(contract.end_date)}</td>
                    <td className="px-4 py-3">
                      <button
                        type="button"
                        onClick={() => planFromContract(contract)}
                        className="text-emerald-700 hover:text-emerald-900 font-medium text-sm"
                      >
                        Tính kế hoạch
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

export default SupplyContractsPage;

