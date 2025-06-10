import { Check, Copy, Plus, Send, Trash2 } from 'lucide-react';
import React, { useState } from 'react';

interface KeyValuePair {
    key: string;
    value: string;
}

interface ApiResponse {
    data: any;
    status: number;
    statusText: string;
    headers: any;
    duration: number;
}

const ApiTester: React.FC = () => {
    const [url, setUrl] = useState<string>('');
    const [method, setMethod] = useState<string>('GET');
    const [payload, setPayload] = useState<KeyValuePair[]>([{ key: '', value: '' }]);
    const [headers, setHeaders] = useState<KeyValuePair[]>([
        { key: 'Content-Type', value: 'application/json' },
        { key: 'Accept', value: 'application/json' },
        { key: 'X-CSRF-TOKEN', value: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
    ]);
    const [response, setResponse] = useState<ApiResponse | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);
    const [copied, setCopied] = useState<boolean>(false);

    const httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];
    const methodsWithPayload = ['POST', 'PUT', 'PATCH'];

    const addPayloadField = () => {
        setPayload([...payload, { key: '', value: '' }]);
    };

    const removePayloadField = (index: number) => {
        setPayload(payload.filter((_, i) => i !== index));
    };

    const updatePayloadField = (index: number, field: 'key' | 'value', value: string) => {
        const updated = payload.map((item, i) => (i === index ? { ...item, [field]: value } : item));
        setPayload(updated);
    };

    const addHeaderField = () => {
        setHeaders([...headers, { key: '', value: '' }]);
    };

    const removeHeaderField = (index: number) => {
        setHeaders(headers.filter((_, i) => i !== index));
    };

    const updateHeaderField = (index: number, field: 'key' | 'value', value: string) => {
        const updated = headers.map((item, i) => (i === index ? { ...item, [field]: value } : item));
        setHeaders(updated);
    };

    const buildPayloadObject = (): any => {
        const obj: any = {};
        payload.forEach((item) => {
            if (item.key.trim() && item.value.trim()) {
                // Try to parse JSON values, fallback to string
                try {
                    obj[item.key] = JSON.parse(item.value);
                } catch {
                    obj[item.key] = item.value;
                }
            }
        });
        return obj;
    };

    const buildHeadersObject = (): any => {
        const obj: any = {};

        headers.forEach((item) => {
            if (item.key.trim() && item.value.trim()) {
                obj[item.key] = item.value;
            }
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken && !obj['X-CSRF-TOKEN']) {
            obj['X-CSRF-TOKEN'] = csrfToken;
        }

        return obj;
    };

    const makeApiCall = async () => {
        if (!url.trim()) {
            setError('URL is required');
            return;
        }

        setLoading(true);
        setError(null);
        setResponse(null);

        const startTime = Date.now();

        try {
            const requestOptions: RequestInit = {
                method: method,
                headers: buildHeadersObject(),
            };

            if (methodsWithPayload.includes(method)) {
                requestOptions.body = JSON.stringify(buildPayloadObject());
            }

            const res = await fetch(url, requestOptions);
            const duration = Date.now() - startTime;

            let responseData;
            const contentType = res.headers.get('content-type');

            if (contentType && contentType.includes('application/json')) {
                responseData = await res.json();
            } else {
                responseData = await res.text();
            }

            // Convert headers to object
            const headersObj: any = {};
            res.headers.forEach((value, key) => {
                headersObj[key] = value;
            });

            setResponse({
                data: responseData,
                status: res.status,
                statusText: res.statusText,
                headers: headersObj,
                duration,
            });
        } catch (err: any) {
            const duration = Date.now() - startTime;
            setError(err.message || 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const copyResponse = async () => {
        if (response) {
            try {
                await navigator.clipboard.writeText(JSON.stringify(response.data, null, 2));
                setCopied(true);
                setTimeout(() => setCopied(false), 2000);
            } catch (err) {
                console.error('Failed to copy response');
            }
        }
    };

    const getStatusColor = (status: number) => {
        if (status >= 200 && status < 300) return 'text-green-600 bg-green-50';
        if (status >= 300 && status < 400) return 'text-yellow-600 bg-yellow-50';
        if (status >= 400 && status < 500) return 'text-orange-600 bg-orange-50';
        return 'text-red-600 bg-red-50';
    };

    return (
        <div className="mx-auto max-w-6xl bg-white p-6">
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {/* Request Panel */}
                <div className="space-y-6">
                    <div className="rounded-lg bg-gray-50 p-6">
                        <h2 className="mb-6 text-2xl font-bold text-gray-800">API Tester</h2>

                        {/* URL and Method */}
                        <div className="space-y-4">
                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">URL</label>
                                <input
                                    type="text"
                                    value={url}
                                    onChange={(e) => setUrl(e.target.value)}
                                    placeholder="https://api.example.com/endpoint"
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label className="mb-2 block text-sm font-medium text-gray-700">Method</label>
                                <select
                                    value={method}
                                    onChange={(e) => setMethod(e.target.value)}
                                    className="w-full rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                                    {httpMethods.map((m) => (
                                        <option key={m} value={m}>
                                            {m}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        {/* Headers */}
                        <div className="mt-6">
                            <div className="mb-3 flex items-center justify-between">
                                <label className="block text-sm font-medium text-gray-700">Headers</label>
                                <button onClick={addHeaderField} className="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                    <Plus size={16} className="mr-1" />
                                    Add Header
                                </button>
                            </div>
                            <div className="space-y-2">
                                {headers.map((header, index) => (
                                    <div key={index} className="flex gap-2">
                                        <input
                                            type="text"
                                            placeholder="Header name"
                                            value={header.key}
                                            onChange={(e) => updateHeaderField(index, 'key', e.target.value)}
                                            className="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        />
                                        <input
                                            type="text"
                                            placeholder="Header value"
                                            value={header.value}
                                            onChange={(e) => updateHeaderField(index, 'value', e.target.value)}
                                            className="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        />
                                        <button onClick={() => removeHeaderField(index)} className="px-3 py-2 text-red-600 hover:text-red-800">
                                            <Trash2 size={16} />
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Payload (for POST, PUT, PATCH) */}
                        {methodsWithPayload.includes(method) && (
                            <div className="mt-6">
                                <div className="mb-3 flex items-center justify-between">
                                    <label className="block text-sm font-medium text-gray-700">Payload</label>
                                    <button onClick={addPayloadField} className="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                        <Plus size={16} className="mr-1" />
                                        Add Field
                                    </button>
                                </div>
                                <div className="space-y-2">
                                    {payload.map((field, index) => (
                                        <div key={index} className="flex gap-2">
                                            <input
                                                type="text"
                                                placeholder="Key"
                                                value={field.key}
                                                onChange={(e) => updatePayloadField(index, 'key', e.target.value)}
                                                className="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                            />
                                            <input
                                                type="text"
                                                placeholder="Value (JSON supported)"
                                                value={field.value}
                                                onChange={(e) => updatePayloadField(index, 'value', e.target.value)}
                                                className="flex-1 rounded-md border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                            />
                                            <button onClick={() => removePayloadField(index)} className="px-3 py-2 text-red-600 hover:text-red-800">
                                                <Trash2 size={16} />
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Send Button */}
                        <div className="mt-6">
                            <button
                                onClick={makeApiCall}
                                disabled={loading}
                                className="flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-3 text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-blue-400"
                            >
                                {loading ? (
                                    <div className="mr-2 h-5 w-5 animate-spin rounded-full border-b-2 border-white"></div>
                                ) : (
                                    <Send size={18} className="mr-2" />
                                )}
                                {loading ? 'Sending...' : 'Make API Call'}
                            </button>
                        </div>
                    </div>
                </div>

                {/* Response Panel */}
                <div className="space-y-6">
                    <div className="rounded-lg bg-gray-50 p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-xl font-semibold text-gray-800">Response</h3>
                            {response && (
                                <button onClick={copyResponse} className="flex items-center text-sm text-blue-600 hover:text-blue-800">
                                    {copied ? <Check size={16} className="mr-1" /> : <Copy size={16} className="mr-1" />}
                                    {copied ? 'Copied!' : 'Copy'}
                                </button>
                            )}
                        </div>

                        {error && (
                            <div className="rounded-md border border-red-200 bg-red-50 p-4">
                                <p className="font-medium text-red-800">Error:</p>
                                <p className="text-red-700">{error}</p>
                            </div>
                        )}

                        {response && (
                            <div className="space-y-4">
                                {/* Status Info */}
                                <div className="flex items-center gap-4 rounded-md border bg-white p-3">
                                    <span className={`rounded-full px-3 py-1 text-sm font-medium ${getStatusColor(response.status)}`}>
                                        {response.status} {response.statusText}
                                    </span>
                                    <span className="text-sm text-gray-600">{response.duration}ms</span>
                                </div>

                                {/* Response Data */}
                                <div className="rounded-md border bg-white">
                                    <div className="rounded-t-md border-b bg-gray-100 px-4 py-2">
                                        <h4 className="font-medium text-gray-700">Response Body</h4>
                                    </div>
                                    <div className="p-4">
                                        <pre className="max-h-96 overflow-auto rounded-md bg-gray-50 p-4 text-sm text-gray-800">
                                            {JSON.stringify(response.data, null, 2)}
                                        </pre>
                                    </div>
                                </div>

                                {/* Response Headers */}
                                <div className="rounded-md border bg-white">
                                    <div className="rounded-t-md border-b bg-gray-100 px-4 py-2">
                                        <h4 className="font-medium text-gray-700">Response Headers</h4>
                                    </div>
                                    <div className="p-4">
                                        <pre className="max-h-48 overflow-auto rounded-md bg-gray-50 p-4 text-sm text-gray-800">
                                            {JSON.stringify(response.headers, null, 2)}
                                        </pre>
                                    </div>
                                </div>
                            </div>
                        )}

                        {!response && !error && !loading && (
                            <div className="py-12 text-center text-gray-500">
                                <Send size={48} className="mx-auto mb-4 opacity-50" />
                                <p>Make an API call to see the response here</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default ApiTester;
